<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<\\Database\\Factories\\ProductFactory> */
    use HasFactory, BelongsToTenant;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    // Convenience accessor for tenant-aware image URL
    public function getProductImageUrlAttribute(): ?string
    {
        $path = $this->attributes['product_image'] ?? null;
        if (! $path) {
            return null;
        }
        if (function_exists('tenant_asset')) {
            $tenantId = function_exists('tenant') ? tenant('id') : null;
            if ($tenantId) {
                return route('stancl.tenancy.asset', ['path' => $path, 'tenant' => $tenantId]);
            }
        }
        return Storage::disk('public')->url($path);
    }
}
