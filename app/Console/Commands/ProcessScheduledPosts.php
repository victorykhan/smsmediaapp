<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostVersion;
use App\Services\PublishService;
use Illuminate\Console\Command;

class ProcessScheduledPosts extends Command
{
    protected $signature = 'app:process-scheduled-posts';

    protected $description = 'Publish scheduled posts whose scheduled time has passed';

    public function handle(PublishService $publish): int
    {
        $dueVersions = PostVersion::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get()
            ->groupBy('post_id');

        foreach ($dueVersions as $postId => $versions) {
            $post = Post::find($postId);

            if (!$post || $post->status !== 'scheduled') {
                continue;
            }

            $this->info("Publishing scheduled post [{$postId}]...");
            $publish->publishNow($post);
            $this->info("Post [{$postId}] published.");
        }

        $count = $dueVersions->count();
        $this->info("Processed {$count} scheduled post(s).");

        return Command::SUCCESS;
    }
}
