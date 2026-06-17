<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class TokenManager
{
    public function encrypt(array $credentials): string
    {
        return Crypt::encryptString(json_encode($credentials));
    }

    public function decrypt(string $encrypted): array
    {
        return json_decode(Crypt::decryptString($encrypted), true) ?? [];
    }

    public function needsRefresh(array $credentials): bool
    {
        if (!isset($credentials['expires_at'])) return false;
        return now()->subDay()->gt($credentials['expires_at']);
    }
}
