<?php

namespace App\Services;

use App\Jobs\SchedulePost as SchedulePostJob;
use App\Models\Post;
use App\Models\PostVersion;
use App\Services\Platforms\PlatformFactory;
use Illuminate\Support\Facades\DB;

class PublishService
{
    public function __construct(
        private PlatformFactory $platforms
    ) {}

    public function publishNow(Post $post): void
    {
        $post->loadMissing('versions.socialAccount');

        DB::transaction(function () use ($post) {
            $post->update(['status' => 'publishing']);

            foreach ($post->versions as $version) {
                try {
                    $service = $this->platforms->make($version->platform);
                    $hasPreUploaded = !empty($version->platform_media_ids)
                        && $version->media_status === 'uploaded';

                    $platformPostId = $hasPreUploaded
                        ? $service->publish($version->socialAccount, $version)
                        : $service->post($version->socialAccount, $version);

                    $version->update([
                        'status' => 'published',
                        'published_at' => now(),
                        'platform_post_id' => $platformPostId,
                    ]);
                } catch (\Throwable $e) {
                    $version->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }

            $hasFailures = $post->versions()->where('status', 'failed')->exists();
            $allPublished = $post->versions()->where('status', 'published')->count() === $post->versions()->count();

            if ($allPublished) {
                $post->update(['status' => 'published']);
            } elseif ($hasFailures) {
                $post->update(['status' => 'failed']);
            }
        });
    }

    public function schedule(Post $post, \DateTime $scheduledAt): void
    {
        $post->update(['status' => 'scheduled']);
        $post->versions()->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
        ]);

        $delay = now()->diffInSeconds($scheduledAt, false);

        if ($delay > 0) {
            SchedulePostJob::dispatch($post)->delay($scheduledAt);
        } else {
            $this->publishNow($post);
        }
    }
}
