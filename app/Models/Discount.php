<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Discount extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'discount_type',
        'value',
        'applicable_for',
        'products',
        'valid_from',
        'valid_till',
        'days',
        'quantity_type',
        'quantity',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_till' => 'date',
        'products' => 'array',
        'days' => 'array',
        'quantity' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        if (! $tenantId) {
            return $query;
        }

        return $query->where('tenant_id', $tenantId);
    }

    public function scopeAvailableForDate(Builder $query, ?CarbonInterface $date = null): Builder
    {
        $date = $date ?: now();
        $weekday = strtolower($date->format('l'));

        return $query
            ->where('status', 'active')
            ->whereDate('valid_from', '<=', $date)
            ->whereDate('valid_till', '>=', $date)
            ->whereJsonContains('days', $weekday)
            ->where(function (Builder $inner) {
                $inner->where('quantity_type', 'unlimited')
                    ->orWhere(function (Builder $decrementing) {
                        $decrementing->where('quantity_type', 'decrement')
                            ->where('quantity', '>', 0);
                    });
            });
    }

    public function appliesToProduct(?int $productId): bool
    {
        if ($this->applicable_for === 'all') {
            return true;
        }

        if ($productId === null) {
            return false;
        }

        $ids = collect($this->products ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        return in_array((int) $productId, $ids, true);
    }

    public function discountAmountFor(float $price): float
    {
        if ($price <= 0) {
            return 0;
        }

        $amount = $this->discount_type === 'percent'
            ? $price * ((float) $this->value / 100)
            : (float) $this->value;

        $amount = min($amount, $price);

        return max($amount, 0);
    }
}
