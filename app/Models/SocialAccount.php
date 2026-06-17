<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $fillable = [
        'client_id', 'platform', 'account_name', 'account_id',
        'avatar_url', 'credentials', 'settings', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }
}
