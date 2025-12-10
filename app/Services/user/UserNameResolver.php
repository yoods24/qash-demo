<?php
namespace App\Services\User;

use Illuminate\Contracts\Auth\Authenticatable;

class UserNameResolver
{
    public static function resolve(?Authenticatable $user): ?string
    {
        if (! $user) return null;

        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));
        $fallback = $user->email ?? null;

        $full = trim($first . ' ' . $last);

        return $full !== '' ? $full : $fallback;
    }
}
