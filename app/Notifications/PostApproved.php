<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostApproved extends Notification
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
            'message' => 'Your post has been approved and published.',
            'post_id' => $this->post->id,
            'client_id' => $this->post->client_id,
            'action_url' => route('posts.show', [$this->post->client_id, $this->post->id]),
        ];
    }
}
