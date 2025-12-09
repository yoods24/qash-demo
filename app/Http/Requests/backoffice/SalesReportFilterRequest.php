<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class SalesReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'granularity' => ['nullable', 'in:daily,monthly'],
            'status' => ['nullable', 'string'],
            'payment_status' => ['nullable', 'in:paid,pending,failed,cancelled'],
        ];
    }

    public function startDate(): Carbon
    {
        $value = $this->input('start_date');

        return $value
            ? Carbon::parse($value)->startOfDay()
            : now()->startOfMonth();
    }

    public function endDate(): Carbon
    {
        $value = $this->input('end_date');

        return $value
            ? Carbon::parse($value)->endOfDay()
            : now()->endOfMonth();
    }

    public function granularity(): string
    {
        return (string) $this->input('granularity', 'daily');
    }

    public function paymentStatus(): string
    {
        return (string) $this->input('payment_status', 'paid');
    }
}
