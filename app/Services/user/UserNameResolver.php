<?php
namespace App\Services\User;

use Illuminate\Contracts\Auth\Authenticatable;

class UserNameResolver
{
    public static function resolve(?Authenticatable $user): ?string
    {
        if (! $user) return null;

        $first = trim((string) ($user->firstName ?? ''));
        $last = trim((string) ($user->lastName ?? ''));
        $fallback = $user->email ?? null;

        $full = trim($first . ' ' . $last);

        return $full !== '' ? $full : $fallback;
    }
}
