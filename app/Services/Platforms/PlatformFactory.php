<?php

namespace App\Services\Platforms;

use App\Services\Platforms\Contracts\PlatformInterface;

class PlatformFactory
{
    private array $services = [];

    public function __construct()
    {
        $this->services = [
            'bluesky' => BlueskyService::class,
            'mastodon' => MastodonService::class,
            'x' => XTwitterService::class,
            'instagram' => InstagramService::class,
            'threads' => ThreadsService::class,
            'facebook' => FacebookPageService::class,
            'youtube' => YouTubeService::class,
            'tiktok' => TikTokService::class,
            'pinterest' => PinterestService::class,
            'linkedin_profile' => LinkedInProfileService::class,
            'linkedin_company' => LinkedInCompanyService::class,
        ];
    }

    public function make(string $platform): PlatformInterface
    {
        $class = $this->services[$platform] ?? null;

        if (!$class) {
            throw new \InvalidArgumentException("No service registered for platform: {$platform}");
        }

        return app($class);
    }

    public function register(string $platform, string $serviceClass): void
    {
        $this->services[$platform] = $serviceClass;
    }
}
