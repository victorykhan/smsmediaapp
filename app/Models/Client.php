<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id', 'name', 'website', 'industry', 'timezone',
        'brand_colors', 'logo', 'onboarding_step', 'is_onboarded',
    ];

    protected $casts = [
        'brand_colors' => 'array',
        'is_onboarded' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function allUsers()
    {
        return $this->users->merge([$this->owner]);
    }

    public function userRole(User $user): ?string
    {
        if ($user->id === $this->user_id) return 'owner';
        $member = $this->users()->where('user_id', $user->id)->first();
        return $member?->pivot->role;
    }

    public function canPostDirectly(User $user): bool
    {
        $role = $this->userRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    public function canApprove(User $user): bool
    {
        $role = $this->userRole($user);
        return in_array($role, ['owner', 'admin']);
    }

    public function canView(User $user): bool
    {
        $role = $this->userRole($user);
        return $role !== null;
    }
}
