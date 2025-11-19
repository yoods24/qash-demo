<?php

namespace App\Services;

use App\Data\TaxCalculationResult;
use App\Models\Tax;

class OrderTaxCalculator
{
    public function calculate(?string $tenantId, float $subtotal): TaxCalculationResult
    {
        $subtotal = max(0, round($subtotal, 2));

        if (! $tenantId) {
            return new TaxCalculationResult(
                $subtotal,
                0.0,
                $subtotal,
                collect()
            );
        }

        $taxes = Tax::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $lines = collect();
        $totalTax = 0.0;

        foreach ($taxes as $tax) {
            $rate = (float) $tax->rate;
            $amount = $tax->type === 'percentage'
                ? round(($rate / 100) * $subtotal, 2)
                : round($rate, 2);

            $lines->push([
                'tax_id' => $tax->id,
                'name' => $tax->name,
                'type' => $tax->type,
                'rate' => $rate,
                'amount' => $amount,
            ]);

            $totalTax += $amount;
        }

        $totalTax = round($totalTax, 2);

        return new TaxCalculationResult(
            $subtotal,
            $totalTax,
            round($subtotal + $totalTax, 2),
            $lines
        );
    }
}
