<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ThreadsService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $userId = $creds['user_id'] ?? $account->account_id;
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;
            $url = Storage::disk('public')->url($path);
            $mime = Storage::disk('public')->mimeType($path);
            $isVideo = str_starts_with($mime, 'video/');

            $response = Http::post("https://graph.threads.net/v1.0/{$userId}/threads", [
                'media_type' => $isVideo ? 'VIDEO' : 'IMAGE',
                ($isVideo ? 'video_url' : 'image_url') => $url,
                'access_token' => $token,
                'is_carousel_item' => count($media) > 1,
            ]);

            if ($response->successful()) {
                $ids[] = $response->json('id');
            }
        }
        return $ids;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $userId = $creds['user_id'] ?? $account->account_id;

        $mediaIds = $version->platform_media_ids ?? [];

        if (count($mediaIds) > 1) {
            $containerData = [
                'media_type' => 'CAROUSEL',
                'children' => $mediaIds,
                'text' => $version->content ?? '',
                'access_token' => $token,
            ];
        } elseif (!empty($mediaIds)) {
            $containerData = [
                'media_type' => 'IMAGE',
                'image_url' => 'placeholder', // Will use existing child container
                'children' => $mediaIds,
                'text' => $version->content ?? '',
                'access_token' => $token,
            ];
        } else {
            $containerData = [
                'media_type' => 'TEXT',
                'text' => $version->content ?? '',
                'access_token' => $token,
            ];
        }

        $response = Http::post("https://graph.threads.net/v1.0/{$userId}/threads", $containerData);
        if ($response->failed()) {
            throw new \Exception('Threads container creation failed: ' . $response->body());
        }
        $containerId = $response->json('id');

        $publishResponse = Http::post("https://graph.threads.net/v1.0/{$userId}/threads_publish", [
            'creation_id' => $containerId,
            'access_token' => $token,
        ]);

        if ($publishResponse->failed()) {
            throw new \Exception('Threads publish failed: ' . $publishResponse->body());
        }
        return $publishResponse->json('id');
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
            $response = Http::get('https://graph.threads.net/v1.0/me', [
                'access_token' => $token,
            ]);
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
}
