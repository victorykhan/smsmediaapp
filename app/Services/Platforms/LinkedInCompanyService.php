<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LinkedInCompanyService implements PlatformInterface
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

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->tokenManager->decrypt($account->credentials);
        $token = $creds['access_token'] ?? '';
        $orgId = $creds['organization_id'] ?? throw new \Exception('LinkedIn Company: missing organization_id in credentials');
        $owner = str_starts_with($orgId, 'urn:li:organization:') ? $orgId : "urn:li:organization:{$orgId}";
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $ids = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;

            $fileContents = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);

            $register = Http::withToken($token)
                ->withHeaders($this->headers($token))
                ->post(self::API_BASE . '/rest/images?action=initializeUpload', [
                    'initializeUploadRequest' => [
                        'owner' => $owner,
                    ],
                ]);

            if ($register->failed()) continue;

            $uploadUrl = $register->json('value.uploadUrl');
            $imageUrn = $register->json('value.image');

            if (!$uploadUrl || !$imageUrn) continue;

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
        $orgId = $creds['organization_id'] ?? throw new \Exception('LinkedIn Company: missing organization_id in credentials');
        $author = str_starts_with($orgId, 'urn:li:organization:') ? $orgId : "urn:li:organization:{$orgId}";

        $body = [
            'author' => $author,
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
            $body['content'] = [
                'media' => ['id' => $mediaIds[0], 'altText' => ''],
            ];
        }

        $response = Http::withToken($token)
            ->withHeaders($this->headers($token))
            ->post(self::API_BASE . '/rest/posts', $body);

        if ($response->failed()) {
            throw new \Exception('LinkedIn Company publish failed: ' . $response->body());
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
                ->get(self::API_BASE . '/rest/organizationalEntityAcls?q=roleAssignee');
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
