<?php

declare(strict_types=1);

namespace App\Http\Requests\backoffice\InvoiceSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_logo' => ['nullable', 'image', 'max:5120'],
            'invoice_prefix' => ['nullable', 'string', 'max:50'],
            'invoice_due_days' => ['required', 'integer', 'min:0', 'max:60'],
            'invoice_round_off' => ['sometimes', 'boolean'],
            'invoice_round_direction' => ['nullable', 'in:up,down'],
            'show_company_details' => ['sometimes', 'boolean'],
            'invoice_header_terms' => ['nullable', 'string'],
            'invoice_footer_terms' => ['nullable', 'string'],
        ];
    }
}
