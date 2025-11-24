<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

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
        return tenant_storage_url($this->attributes['product_image'] ?? null);
    }
}
