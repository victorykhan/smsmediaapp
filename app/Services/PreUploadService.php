<?php

namespace App\Services;

use App\Models\PostVersion;
use App\Models\SocialAccount;
use App\Services\Platforms\PlatformFactory;
use Illuminate\Support\Facades\DB;

class PreUploadService
{
    public function __construct(
        private PlatformFactory $platforms
    ) {}

    /**
     * Upload media for a single version to its platform.
     * Stores platform media IDs in the version record.
     */
    public function uploadVersionMedia(PostVersion $version): void
    {
        $media = $version->media ?? [];
        if (empty($media)) {
            $version->update(['media_status' => 'no_media']);
            return;
        }

        try {
            $service = $this->platforms->make($version->platform);
            $mediaIds = $service->uploadMedia($version->socialAccount, $version);

            $version->update([
                'platform_media_ids' => $mediaIds,
                'media_status' => 'uploaded',
            ]);
        } catch (\Throwable $e) {
            $version->update([
                'media_status' => 'failed',
                'error_message' => 'Media pre-upload failed: ' . $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Upload media for all versions of a post.
     * Each version is uploaded independently — failures don't block others.
     * Returns array of results keyed by version id.
     */
    public function uploadPostMedia(\App\Models\Post $post): array
    {
        $results = [];

        foreach ($post->versions as $version) {
            try {
                $this->uploadVersionMedia($version);
                $results[$version->id] = ['success' => true, 'platform' => $version->platform];
            } catch (\Throwable $e) {
                $results[$version->id] = [
                    'success' => false,
                    'platform' => $version->platform,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Retry failed media uploads for a version.
     */
    public function retryVersionMedia(PostVersion $version): bool
    {
        try {
            $this->uploadVersionMedia($version);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
