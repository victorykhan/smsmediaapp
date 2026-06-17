<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LinkedInProfileService implements PlatformInterface
{
    private const API_BASE = 'https://api.linkedin.com';

    public function __construct(
        private TokenManager $tokenManager
    ) {}

    private function headers(string $token): array
    {
        return [
            'X-Restli-Protocol-Version' => '2.0.0',
            'LinkedIn-Version' => '202606',
        ];
    }

    private function getPersonId(array $creds): string
    {
        return $creds['person_id'] ?? throw new \Exception('LinkedIn Profile: missing person_id in credentials');
    }

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $personId = $this->getPersonId($creds);
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;

            $fileContents = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);

            // Step 1: Register image upload
            $register = Http::withToken($token)
                ->withHeaders($this->headers($token))
                ->post(self::API_BASE . '/rest/images?action=initializeUpload', [
                    'initializeUploadRequest' => [
                        'owner' => "urn:li:person:{$personId}",
                    ],
                ]);

            if ($register->failed()) continue;

            $uploadUrl = $register->json('value.uploadUrl');
            $imageUrn = $register->json('value.image');

            if (!$uploadUrl || !$imageUrn) continue;

            // Step 2: Upload binary
            $upload = Http::withOptions([
                'headers' => ['Content-Type' => $mimeType],
            ])->withBody($fileContents, $mimeType)->put($uploadUrl);

            if ($upload->failed()) continue;

            $ids[] = $imageUrn;
        }
        return $ids;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $personId = $this->getPersonId($creds);

        $body = [
            'author' => "urn:li:person:{$personId}",
            'commentary' => $version->content ?? '',
            'visibility' => 'PUBLIC',
            'distribution' => [
                'feedDistribution' => 'MAIN_FEED',
                'targetEntities' => [],
                'thirdPartyDistributionChannels' => [],
            ],
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];

        $mediaIds = $version->platform_media_ids ?? [];
        if (!empty($mediaIds)) {
            $body['content'] = array_map(fn($urn) => [
                'type' => 'IMAGE',
                'media' => ['id' => $urn, 'altText' => ''],
            ], $mediaIds);
        }

        $response = Http::withToken($token)
            ->withHeaders($this->headers($token))
            ->post(self::API_BASE . '/rest/posts', $body);

        if ($response->failed()) {
            throw new \Exception('LinkedIn Profile publish failed: ' . $response->body());
        }

        return $response->header('x-restli-id') ?? $response->json('id') ?? 'published';
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
                ->withHeaders($this->headers($token))
                ->get(self::API_BASE . '/rest/me');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 3000,
            'media_allowed' => ['image', 'video'],
            'media_required' => null,
            'supports_text_only' => true,
        ];
    }
}
