<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'tax_id',
        'name',
        'type',
        'rate',
        'amount',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
