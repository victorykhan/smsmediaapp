<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class InstagramService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    private const API_VERSION = 'v22.0';

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? $creds['page_access_token'] ?? '';
        $igUserId = $creds['ig_user_id'] ?? $account->account_id;
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;
            $url = Storage::disk('public')->url($path);
            $mime = Storage::disk('public')->mimeType($path);
            $isVideo = str_starts_with($mime, 'video/');

            $response = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$igUserId}/media", [
                'media_type' => $isVideo ? 'VIDEO' : 'IMAGE',
                ($isVideo ? 'video_url' : 'image_url') => $url,
                'access_token' => $token,
                'published' => 'false',
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
        $token = $creds['access_token'] ?? $creds['page_access_token'] ?? '';
        $igUserId = $creds['ig_user_id'] ?? $account->account_id;

        $mediaIds = $version->platform_media_ids ?? [];
        if (empty($mediaIds)) {
            throw new \Exception('Instagram requires media. No pre-uploaded media found.');
        }

        $containerId = $mediaIds[0];
        $caption = $version->content ?? '';

        if (count($mediaIds) > 1) {
            $carouselResponse = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$igUserId}/media", [
                'media_type' => 'CAROUSEL',
                'children' => $mediaIds,
                'caption' => $caption,
                'access_token' => $token,
            ]);
            if ($carouselResponse->failed()) {
                throw new \Exception('Instagram carousel creation failed: ' . $carouselResponse->body());
            }
            $containerId = $carouselResponse->json('id');
        }

        $publishResponse = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$igUserId}/media_publish", [
            'creation_id' => $containerId,
            'access_token' => $token,
        ]);

        if ($publishResponse->failed()) {
            throw new \Exception('Instagram publish failed: ' . $publishResponse->body());
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
            $token = $creds['access_token'] ?? $creds['page_access_token'] ?? '';
            $igUserId = $creds['ig_user_id'] ?? $account->account_id;
            $response = Http::get("https://graph.facebook.com/" . self::API_VERSION . "/{$igUserId}", [
                'access_token' => $token,
                'fields' => 'id,name',
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 2200,
            'media_allowed' => ['image', 'video'],
            'media_required' => 'image_or_video',
            'supports_text_only' => false,
        ];
    }
}
