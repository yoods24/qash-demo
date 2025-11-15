<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_detail_id',
        'total',
        'status',
        'payment_status',
        'reference_no',
        'source',
        'order_type',
        'confirmed_at', 'preparing_at', 'ready_at',
        'expected_seconds_total',
        'queue_seconds', 'prep_seconds', 'total_seconds',
    ];

    public function customerDetail()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_detail_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    protected $casts = [
        'confirmed_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expected_seconds_total' => 'integer',
        'queue_seconds' => 'integer',
        'prep_seconds' => 'integer',
        'total_seconds' => 'integer',
    ];

    // Computed durations derived from timestamps (source of truth for UI)
    public function getComputedQueueSecondsAttribute(): int
    {
        $confirmed = $this->confirmed_at;
        $preparing = $this->preparing_at;
        if ($confirmed && $preparing) {
            return max(0, $preparing->diffInSeconds($confirmed));
        }
        return 0;
    }

    public function getComputedPrepSecondsAttribute(): int
    {
        $preparing = $this->preparing_at;
        $ready = $this->ready_at;
        if ($preparing && $ready) {
            return max(0, $ready->diffInSeconds($preparing));
        }
        return 0;
    }

    public function getComputedTotalSecondsAttribute(): int
    {
        $q = (int) $this->computed_queue_seconds;
        $p = (int) $this->computed_prep_seconds;
        if ($this->ready_at && $this->confirmed_at) {
            // Prefer sum of segments, but guard with whole-span diff
            $span = max(0, $this->ready_at->diffInSeconds($this->confirmed_at));
            return max($q + $p, $span);
        }
        return $q + $p;
    }
}
