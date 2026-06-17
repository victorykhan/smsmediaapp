<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TikTokService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        return [];
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        return $this->post($account, $version);
    }

    public function post(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->ensureFreshToken($account);
        $accessToken = $creds['access_token'];

        $media = $version->media ?? [];
        if (empty($media)) {
            throw new \Exception('TikTok requires a video to post.');
        }

        $videoPath = $media[0];
        if (!Storage::disk('public')->exists($videoPath)) {
            throw new \Exception('Video file not found: ' . $videoPath);
        }

        $videoSize = Storage::disk('public')->size($videoPath);
        $videoContent = Storage::disk('public')->get($videoPath);
        $mimeType = Storage::disk('public')->mimeType($videoPath);

        $initResponse = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
            ->post('https://open.tiktokapis.com/v2/post/publish/video/init/', [
                'post_info' => [
                    'title' => $version->content ?? '',
                    'privacy_level' => 'PUBLIC',
                ],
                'source_info' => [
                    'source' => 'FILE_UPLOAD',
                    'video_size' => $videoSize,
                    'chunk_size' => $videoSize,
                    'total_chunk_count' => 1,
                ],
            ]);

        if ($initResponse->failed()) {
            throw new \Exception('TikTok init failed: ' . $initResponse->body());
        }

        $initData = $initResponse->json('data');
        $publishId = $initData['publish_id'] ?? null;
        $uploadUrl = $initData['upload_url'] ?? null;

        if (!$publishId || !$uploadUrl) {
            throw new \Exception('TikTok init missing publish_id or upload_url');
        }

        $uploadResponse = Http::withHeaders([
            'Content-Range' => 'bytes 0-' . ($videoSize - 1) . '/' . $videoSize,
            'Content-Length' => $videoSize,
            'Content-Type' => $mimeType,
        ])->withBody($videoContent, $mimeType)->put($uploadUrl);

        if ($uploadResponse->failed()) {
            throw new \Exception('TikTok video upload failed: ' . $uploadResponse->body());
        }

        return $this->pollPublishStatus($accessToken, $publishId);
    }

    public function validate(SocialAccount $account): bool
    {
        try {
            $creds = $this->tokenManager->decrypt($account->credentials);
            $response = Http::withToken($creds['access_token'])
                ->get('https://open.tiktokapis.com/v2/user/info/', [
                    'fields' => 'open_id',
                ]);
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 2200,
            'media_allowed' => ['video'],
            'media_required' => 'video',
            'supports_text_only' => false,
        ];
    }

    private function ensureFreshToken(SocialAccount $account): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);

        if (isset($creds['expires_at']) && now()->gte($creds['expires_at'])) {
            return $this->refreshToken($account, $creds);
        }

        return $creds;
    }

    private function refreshToken(SocialAccount $account, array $creds): array
    {
        $refreshToken = $creds['refresh_token'] ?? null;
        if (!$refreshToken) {
            throw new \Exception('TikTok token expired and no refresh_token available. Re-authorize the account.');
        }

        $clientId = config('services.tiktok.client_id');
        $clientSecret = config('services.tiktok.client_secret');

        $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('TikTok token refresh failed: ' . $response->body());
        }

        $data = $response->json();
        $newCreds = [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => isset($data['expires_in'])
                ? now()->addSeconds($data['expires_in'])->toDateTimeString()
                : null,
            'scope' => $data['scope'] ?? '',
        ];

        $account->updateQuietly([
            'credentials' => $this->tokenManager->encrypt($newCreds),
        ]);

        return $newCreds;
    }

    private function pollPublishStatus(string $accessToken, string $publishId, int $maxAttempts = 30): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json; charset=UTF-8'])
                ->post('https://open.tiktokapis.com/v2/post/publish/status/fetch/', [
                    'publish_id' => $publishId,
                ]);

            if ($response->failed()) {
                throw new \Exception('TikTok status check failed: ' . $response->body());
            }

            $status = $response->json('data.status');
            $postId = $response->json('data.post_id');

            if ($status === 'PUBLISH_COMPLETE') {
                if (!$postId) {
                    throw new \Exception('TikTok publish complete but no post_id returned');
                }
                return (string) $postId;
            }

            if (in_array($status, ['FAILED', 'CANCELLED'])) {
                throw new \Exception('TikTok publish failed with status: ' . ($status ?? 'UNKNOWN'));
            }

            sleep(2);
        }

        throw new \Exception('TikTok publish timed out after ' . $maxAttempts . ' attempts (60s)');
    }
}
