<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FacebookPageService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    private const API_VERSION = 'v22.0';

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['page_access_token'] ?? $creds['access_token'] ?? '';
        $pageId = $creds['page_id'] ?? $account->account_id;
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;
            $url = Storage::disk('public')->url($path);
            $mime = Storage::disk('public')->mimeType($path);
            $isVideo = str_starts_with($mime, 'video/');

            if ($isVideo) {
                $response = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$pageId}/videos", [
                    'file_url' => $url,
                    'published' => 'false',
                    'access_token' => $token,
                ]);
            } else {
                $response = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$pageId}/photos", [
                    'url' => $url,
                    'published' => 'false',
                    'access_token' => $token,
                ]);
            }

            if ($response->successful()) {
                $ids[] = $response->json('id');
            }
        }
        return $ids;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['page_access_token'] ?? $creds['access_token'] ?? '';
        $pageId = $creds['page_id'] ?? $account->account_id;
        $mediaIds = $version->platform_media_ids ?? [];

        if (!empty($mediaIds)) {
            $firstId = $mediaIds[0];
            $attach = [];
            foreach ($mediaIds as $mid) {
                $mime = '';
                $media = $version->media ?? [];
                foreach ($media as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        $mime = Storage::disk('public')->mimeType($path);
                        break;
                    }
                }
                $isVideo = str_starts_with($mime, 'video/');
                $attach[] = $isVideo ? "videoid:{$mid}" : "mediaid:{$mid}";
            }

            $response = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$pageId}/feed", [
                'message' => $version->content ?? '',
                'attached_media' => $attach,
                'access_token' => $token,
            ]);
        } else {
            $response = Http::post("https://graph.facebook.com/" . self::API_VERSION . "/{$pageId}/feed", [
                'message' => $version->content ?? '',
                'access_token' => $token,
            ]);
        }

        if ($response->failed()) {
            throw new \Exception('Facebook publish failed: ' . $response->body());
        }
        return $response->json('id') ?? $response->json('post_id');
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
            $token = $creds['page_access_token'] ?? $creds['access_token'] ?? '';
            $pageId = $creds['page_id'] ?? $account->account_id;
            $response = Http::get("https://graph.facebook.com/" . self::API_VERSION . "/{$pageId}", [
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
            'text_limit' => 63206,
            'media_allowed' => ['image', 'video', 'link'],
            'media_required' => null,
            'supports_text_only' => true,
        ];
    }
}
