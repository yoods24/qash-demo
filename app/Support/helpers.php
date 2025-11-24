<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('rupiahIdr')) {
    function rupiahIdr($number): string
    {
        return 'IDR ' . number_format((float) $number, 0, ',', '.');
    }
}

if (! function_exists('rupiahRp')) {
    function rupiahRp($number): string
    {
        return 'Rp ' . number_format((float) $number, 0, ',', '.');
    }
}

if (! function_exists('rupiah')) {
    function rupiah($number): string
    {
        return rupiahIdr($number);
    }
}

if (! function_exists('tenant_storage_url')) {
    function tenant_storage_url(?string $path, ?string $tenantId = null): ?string
    {
        if (! $path) {
            return null;
        }

        $normalizedPath = ltrim($path, '/');
        $tenantId = $tenantId
            ?? (function_exists('tenant') ? tenant('id') : null)
            ?? optional(request()->route())->parameter('tenant');

        if ($tenantId) {
            return route('stancl.tenancy.asset', [
                'tenant' => $tenantId,
                'path' => $normalizedPath,
            ]);
        }

        if (class_exists(Storage::class)) {
            return Storage::disk('public')->url($normalizedPath);
        }

        return asset('storage/' . $normalizedPath);
    }
}
