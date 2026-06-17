<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\PublishService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SchedulePost implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Post $post
    ) {}

    public function handle(PublishService $publish): void
    {
        $post = Post::with('versions.socialAccount')->find($this->post->id);

        if (!$post || $post->status !== 'scheduled') {
            return;
        }

        $publish->publishNow($post);
    }
}
