<?php

namespace App\Models;

use App\Jobs\SendInvoiceEmailJob;
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
        'subtotal',
        'total_tax',
        'grand_total',
        'status',
        'payment_status',
        'payment_channel',
        'reference_no',
        'source',
        'order_type',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'paid_at',
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
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'expected_seconds_total' => 'integer',
        'queue_seconds' => 'integer',
        'prep_seconds' => 'integer',
        'total_seconds' => 'integer',
    ];

    public function taxLines()
    {
        return $this->hasMany(OrderTax::class);
    }

    protected static function booted(): void
    {
        static::updated(function (Order $order): void {
            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                SendInvoiceEmailJob::dispatch($order->id, (string) $order->tenant_id);
            }
        });
    }

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

    public function isTakeaway(): bool
    {
        return ($this->order_type ?? 'dine-in') === 'takeaway';
    }

    public function isDineIn(): bool
    {
        return ! $this->isTakeaway();
    }

    public function orderTypeLabel(): string
    {
        if ($this->isTakeaway()) {
            return 'Takeaway';
        }

        $tableLabel = $this->customerDetail?->diningTable?->label;

        return $tableLabel
            ? 'Table ' . $tableLabel
            : 'Dine In';
    }
}
