<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $casts = [
        'data' => 'array',
    ];

    // Human-friendly name stored in tenant data
    public function getNameAttribute(): string
    {
        return $this->data['name'] ?? $this->id;
    }

    // Path/slug alias for clarity in views
    public function getPathAttribute(): string
    {
        return $this->id;
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id')->where('is_admin', true);
    }

    /**
     * Tell Stancl's virtual column trait which columns are real DB columns
     * so attributes like company_code don't get moved into the JSON data.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_code',
            'created_at',
            'updated_at',
            static::getDataColumn(),
        ];
    }
}
