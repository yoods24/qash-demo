<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Career extends Model
{
    /** @use HasFactory<\Database\Factories\CareerFactory> */
    use HasFactory, BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeForCurrentTenant($query)
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;

        if ($tenantId === null && auth()->check()) {
            $tenantId = auth()->user()->tenant_id ?? null;
        }

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    public function getSalaryRangeAttribute(): ?string
    {
        $min = $this->salary_min;
        $max = $this->salary_max;

        if ($min === null && $max === null) {
            return null;
        }

        $format = static function (?int $value): string {
            return 'Rp ' . number_format((int) $value, 0, ',', '.');
        };

        if ($min !== null && $max !== null) {
            if ($min === $max) {
                return $format($min);
            }

            return $format($min) . ' – ' . $format($max);
        }

        return $min !== null ? $format($min) : $format($max);
    }
}
