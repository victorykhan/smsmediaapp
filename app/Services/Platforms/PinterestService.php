<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PinterestService implements PlatformInterface
{
    private const API_BASE = 'https://api.pinterest.com/v5';

    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;

            $fileContents = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);

            // Step 1: Register media upload
            $register = Http::withToken($token)
                ->post(self::API_BASE . '/media', ['media_type' => 'image']);

            if ($register->failed()) continue;

            $mediaId = $register->json('media_id');
            $uploadUrl = $register->json('upload_url');
            $uploadParams = $register->json('upload_parameters') ?? [];

            // Step 2: Upload to signed URL with multipart params
            $multipart = [];
            foreach ($uploadParams as $key => $value) {
                $multipart[] = ['name' => $key, 'contents' => $value];
            }
            $multipart[] = [
                'name' => 'file',
                'contents' => $fileContents,
                'filename' => basename($path),
                'headers' => ['Content-Type' => $mimeType],
            ];

            $upload = Http::asMultipart()->post($uploadUrl, $multipart);
            if ($upload->failed()) continue;

            // Step 3: Poll for completion
            for ($i = 0; $i < 30; $i++) {
                $status = Http::withToken($token)
                    ->get(self::API_BASE . "/media/{$mediaId}");

                if ($status->failed()) break;

                $state = $status->json('status');
                if ($state === 'succeeded') {
                    $ids[] = $mediaId;
                    break;
                }
                if ($state === 'failed') break;

                usleep(1_000_000);
            }
        }
        return $ids;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $boardId = $creds['board_id'] ?? throw new \Exception('Pinterest: missing board_id in credentials (add to credentials JSON)');

        $mediaIds = $version->platform_media_ids ?? [];
        if (empty($mediaIds)) {
            throw new \Exception('Pinterest: no uploaded media to pin');
        }

        $contentType = 'image/jpeg';

        $params = [
            'board_id' => $boardId,
            'description' => $version->content ?? '',
            'media_source' => [
                'source_type' => 'image_id',
                'content_type' => $contentType,
                'data' => $mediaIds[0],
            ],
        ];

        $response = Http::withToken($token)
            ->post(self::API_BASE . '/pins', $params);

        if ($response->failed()) {
            throw new \Exception('Pinterest publish failed: ' . $response->body());
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
            $token = $creds['access_token'] ?? '';
            return Http::withToken($token)
                ->get(self::API_BASE . '/user_account')
                ->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 500,
            'media_allowed' => ['image'],
            'media_required' => 'image_or_video',
            'supports_text_only' => false,
        ];
    }
}
