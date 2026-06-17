<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Post $post) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'A new post is pending approval from ' . ($this->post->user?->name ?? 'a team member') . '.',
            'post_id' => $this->post->id,
            'client_id' => $this->post->client_id,
            'action_url' => route('posts.show', [$this->post->client_id, $this->post->id]),
        ];
    }
}
