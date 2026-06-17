<?php

namespace App\Services\Platforms;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\Contracts\PlatformInterface;
use App\Services\TokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BlueskyService implements PlatformInterface
{
    private ?string $accessJwt = null;
    private ?string $did = null;
    private ?string $pdsEndpoint = null;

    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function uploadMedia(SocialAccount $account, PostVersion $version): array
    {
        $creds = $this->resolveCredentials($account);
        $this->authenticate($creds['handle'], $creds['app_password']);
        $media = $version->media ?? [];
        if (empty($media)) return [];

        $images = [];
        foreach ($media as $path) {
            if (!Storage::disk('public')->exists($path)) continue;
            $fileContents = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);
            if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) continue;

            $blobResponse = Http::withToken($this->accessJwt)
                ->withHeaders(['Content-Type' => $mimeType])
                ->withBody($fileContents, $mimeType)
                ->post($this->pdsEndpoint . '/xrpc/com.atproto.repo.uploadBlob');

            if ($blobResponse->failed()) {
                throw new \Exception('Blob upload failed: ' . $blobResponse->body());
            }
            $images[] = $blobResponse->json('blob');
        }
        return $images;
    }

    public function publish(SocialAccount $account, PostVersion $version): string
    {
        $creds = $this->resolveCredentials($account);
        $this->authenticate($creds['handle'], $creds['app_password']);

        $record = [
            'repo' => $this->did,
            'collection' => 'app.bsky.feed.post',
            'record' => [
                '$type' => 'app.bsky.feed.post',
                'createdAt' => now()->toISOString(),
                'text' => $version->content ?? '',
            ],
        ];

        $blobs = $version->platform_media_ids ?? [];
        if (!empty($blobs)) {
            $record['record']['embed'] = [
                '$type' => 'app.bsky.embed.images',
                'images' => array_map(fn($b) => ['alt' => '', 'image' => $b], $blobs),
            ];
        }

        $response = Http::withToken($this->accessJwt)
            ->post($this->pdsEndpoint . '/xrpc/com.atproto.repo.createRecord', $record);

        if ($response->failed()) {
            throw new \Exception('Bluesky publish failed: ' . $response->body());
        }

        return $response->json('uri') ?? throw new \Exception('Bluesky returned no URI');
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
            $creds = $this->resolveCredentials($account);
            $this->authenticate($creds['handle'], $creds['app_password']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstraints(): array
    {
        return [
            'text_limit' => 300,
            'media_allowed' => ['image'],
            'media_required' => null,
            'supports_text_only' => true,
        ];
    }

    private function resolveCredentials(SocialAccount $account): array
    {
        $raw = $account->credentials;
        try {
            $decrypted = $this->tokenManager->decrypt($raw);
            return [
                'handle' => $decrypted['handle'] ?? $account->account_id,
                'app_password' => $decrypted['app_password'] ?? $decrypted['token'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'handle' => $account->account_id,
                'app_password' => $raw,
            ];
        }
    }

    private function authenticate(string $handle, string $appPassword): void
    {
        $resolveResponse = Http::get('https://bsky.social/xrpc/com.atproto.identity.resolveHandle', [
            'handle' => str_replace('@', '', $handle),
        ]);
        if ($resolveResponse->failed()) {
            throw new \Exception('Could not resolve Bluesky handle: ' . $handle);
        }
        $this->did = $resolveResponse->json('did');

        $didDoc = Http::get('https://plc.directory/' . $this->did);
        if ($didDoc->successful()) {
            $services = $didDoc->json('service', []);
            $pdsEndpoint = null;
            foreach ($services as $svc) {
                if (($svc['type'] ?? '') === 'AtprotoPersonalDataServer') {
                    $pdsEndpoint = $svc['serviceEndpoint'] ?? null;
                    break;
                }
            }
            $this->pdsEndpoint = rtrim($pdsEndpoint ?: 'https://bsky.social', '/');
        } else {
            $this->pdsEndpoint = 'https://bsky.social';
        }

        $authResponse = Http::post($this->pdsEndpoint . '/xrpc/com.atproto.server.createSession', [
            'identifier' => $handle,
            'password' => $appPassword,
        ]);
        if ($authResponse->failed()) {
            throw new \Exception('Bluesky authentication failed: ' . $authResponse->body());
        }
        $this->accessJwt = $authResponse->json('accessJwt');
    }
}
