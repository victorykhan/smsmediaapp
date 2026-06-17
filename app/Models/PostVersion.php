<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostVersion extends Model
{
    protected $fillable = [
        'post_id', 'social_account_id', 'platform', 'content',
        'media', 'platform_media_ids', 'media_status',
        'status', 'scheduled_at', 'published_at',
        'platform_post_id', 'error_message',
    ];

    protected $casts = [
        'media' => 'array',
        'platform_media_ids' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function scopeMediaPending($query)
    {
        return $query->where('media_status', 'pending');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }
}
