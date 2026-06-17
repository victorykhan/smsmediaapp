<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class XTwitterService implements PlatformInterface
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $bearer = $creds['bearer_token'] ?? $creds['access_token'] ?? '';
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            $file = Storage::disk('public')->get($path);
            if (!$file) continue;
            $data = base64_encode($file);

            $response = Http::withToken($bearer)
                ->post('https://upload.twitter.com/1.1/media/upload.json', [
                    'media_data' => $data,
                ]);

            if ($response->successful()) {
                $ids[] = $response->json('media_id_string');
            }
        }
        return $ids;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $bearer = $creds['bearer_token'] ?? $creds['access_token'] ?? '';

        $data = ['text' => $version->content ?? ''];
        $mediaIds = $version->platform_media_ids ?? [];
        if (!empty($mediaIds)) {
            $data['media'] = ['media_ids' => $mediaIds];
        }

        $response = Http::withToken($bearer)
            ->post('https://api.twitter.com/2/tweets', $data);

        if ($response->failed()) {
            throw new \Exception('X/Twitter publish failed: ' . $response->body());
        }
        return $response->json('data.id');
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
            $bearer = $creds['bearer_token'] ?? $creds['access_token'] ?? '';
            $response = Http::withToken($bearer)
                ->get('https://api.twitter.com/2/users/me');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 280,
            'media_allowed' => ['image', 'video'],
            'media_required' => null,
            'supports_text_only' => true,
        ];
    }
}
