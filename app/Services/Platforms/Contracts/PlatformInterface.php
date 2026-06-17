<?php

namespace App\Services\Platforms\Contracts;

use App\Models\PostVersion;
use App\Models\SocialAccount;

interface PlatformInterface
{
    /**
     * Pre-upload media to the platform and return platform media IDs.
     * Called when a post is created/scheduled — stores media as draft on the platform.
     * @return array<string>  Array of platform media IDs
     */
    public function uploadMedia(SocialAccount $account, PostVersion $version): array;

    /**
     * Publish or schedule a post using previously uploaded media IDs.
     * Called at publish time — uses platform_media_ids from PostVersion.
     * @return string  Platform post ID
     */
    public function publish(SocialAccount $account, PostVersion $version): string;

    /**
     * Legacy one-shot post (text + media upload at once). Used for instant publishing.
     * @return string  Platform post ID
     */
    public function post(SocialAccount $account, PostVersion $version): string;

    /**
     * Validate that the account credentials are still valid.
     */
    public function validate(SocialAccount $account): bool;

    /**
     * Get posting constraints for this platform.
     */
    public function getConstraints(): array;
}
