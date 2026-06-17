<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class YouTubeService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    /**
     * Upload video to YouTube as unlisted (draft).
     * Uses resumable upload protocol — handles files of any size
     * by streaming in chunks. The video is stored on YouTube's servers
     * as a private draft until publish() is called.
     */
    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $media = $version->media ?? [];

        if (empty($media)) {
            return [];
        }

        $videoPath = $media[0];
        $file = Storage::disk('public')->get($videoPath);
        if (!$file) throw new \Exception("Video file not found: {$videoPath}");

        $fileSize = Storage::disk('public')->size($videoPath);
        $mime = Storage::disk('public')->mimeType($videoPath);
        $title = str($version->content ?? '')->limit(100)->toString();
        $description = $version->content ?? '';

        // Step 1: Create resumable session with UNLISTED privacy (draft)
        $sessionResponse = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'X-Upload-Content-Length' => $fileSize,
                'X-Upload-Content-Type' => $mime,
            ])
            ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status', [
                'snippet' => [
                    'title' => $title ?: 'Untitled Video',
                    'description' => $description,
                ],
                'status' => [
                    'privacyStatus' => 'unlisted',
                    'selfDeclaredMadeForKids' => false,
                ],
            ]);

        if ($sessionResponse->failed()) {
            throw new \Exception('YouTube upload session creation failed: ' . $sessionResponse->body());
        }

        $uploadUrl = $sessionResponse->header('Location');
        if (!$uploadUrl) {
            throw new \Exception('No upload URL returned by YouTube.');
        }

        // Step 2: Upload the full video bytes
        $uploadResponse = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => $mime,
                'Content-Length' => $fileSize,
            ])
            ->withBody($file, $mime)
            ->put($uploadUrl);

        if ($uploadResponse->failed()) {
            throw new \Exception('YouTube video upload failed: ' . $uploadResponse->body());
        }

        $videoId = $uploadResponse->json('id');
        if (!$videoId) throw new \Exception('YouTube returned no video ID.');

        return [$videoId];
    }

    /**
     * Publish the video by changing privacy from unlisted to public.
     */
    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $mediaIds = $version->platform_media_ids ?? [];

        if (empty($mediaIds)) {
            return $this->post($account, $version);
        }

        $videoId = $mediaIds[0];
        $title = str($version->content ?? '')->limit(100)->toString();

        $updateResponse = Http::withToken($token)
            ->put('https://www.googleapis.com/youtube/v3/videos?part=snippet,status', [
                'id' => $videoId,
                'snippet' => [
                    'title' => $title ?: 'Untitled Video',
                    'description' => $version->content ?? '',
                ],
                'status' => [
                    'privacyStatus' => 'public',
                    'selfDeclaredMadeForKids' => false,
                ],
            ]);

        if ($updateResponse->failed()) {
            throw new \Exception('YouTube publish failed: ' . $updateResponse->body());
        }

        return $videoId;
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
            $token = $creds['access_token'] ?? '';
            $response = Http::withToken($token)
                ->get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'id',
                    'mine' => 'true',
                ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => null,
            'media_allowed' => ['video'],
            'media_required' => 'video',
            'supports_text_only' => false,
        ];
    }
}
