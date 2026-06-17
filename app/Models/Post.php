<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['client_id', 'user_id', 'content', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function versions()
    {
        return $this->hasMany(PostVersion::class);
    }

    public function scopeForClient($query, Client $client)
    {
        return $query->where('client_id', $client->id);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }
}
