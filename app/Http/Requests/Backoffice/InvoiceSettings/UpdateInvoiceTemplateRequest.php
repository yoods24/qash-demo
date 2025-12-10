<?php

declare(strict_types=1);

namespace App\Http\Requests\backoffice\InvoiceSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template' => ['required', 'string', 'in:template_1,template_2,template_3'],
        ];
    }
}
