<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MastodonService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $base = $this->normalizeBase($creds['instance'] ?? $creds['server'] ?? 'mastodon.social');
        $token = $creds['access_token'] ?? $creds['token'] ?? throw new \Exception('Mastodon: missing access_token in credentials');
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;
            $file = Storage::disk('public')->get($path);
            $mime = Storage::disk('public')->mimeType($path);

            $response = Http::withToken($token)
                ->attach('file', $file, basename($path), ['Content-Type' => $mime])
                ->post("{$base}/api/v1/media");

            if ($response->successful()) {
                $ids[] = $response->json('id');
            }
        }
        return $ids;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $base = $this->normalizeBase($creds['instance'] ?? $creds['server'] ?? 'mastodon.social');
        $token = $creds['access_token'] ?? $creds['token'] ?? throw new \Exception('Mastodon: missing access_token in credentials');

        $params = ['status' => $version->content ?? ''];
        $mediaIds = $version->platform_media_ids ?? [];
        if (!empty($mediaIds)) {
            $params['media_ids'] = $mediaIds;
        }

        $response = Http::withToken($token)
            ->post("{$base}/api/v1/statuses", $params);

        if ($response->failed()) {
            throw new \Exception('Mastodon publish failed: ' . $response->body());
        }
        return $response->json('id');
    }

    public function post(SocialAccount $account, PostVersion $version): string
    {
        $ids = $this->uploadMedia($account, $version);
        $version->setAttribute('platform_media_ids', $ids);
        return $this->publish($account, $version);
    }

    public function validate(SocialAccount $account): bool
    {
        try {
            $creds = $this->tokenManager->decrypt($account->credentials);
            $base = $this->normalizeBase($creds['instance'] ?? $creds['server'] ?? 'mastodon.social');
            $token = $creds['access_token'] ?? $creds['token'] ?? '';
            $response = Http::withToken($token)
                ->get("{$base}/api/v1/accounts/verify_credentials");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 500,
            'media_allowed' => ['image', 'video'],
            'media_required' => null,
            'supports_text_only' => true,
        ];
    }

    private function normalizeBase(string $url): string
    {
        $url = trim($url);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        return rtrim($url, '/');
    }
}
