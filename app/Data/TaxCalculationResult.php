<?php

namespace App\Data;

use Illuminate\Support\Collection;

class TaxCalculationResult
{
    public function __construct(
        public float $subtotal,
        public float $totalTax,
        public float $grandTotal,
        public Collection $lines
    ) {
    }
}
