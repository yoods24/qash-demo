<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProfile extends Model
{
    protected $fillable = [
        'tenant_id',
        'brand_heading',
        'brand_slogan',
        'about',
        'contact_email',
        'contact_phone',
        'opening_hours',
        'social_links',
        'address',
        'latitude',
        'longitude',
        'logo_url',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'social_links' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
