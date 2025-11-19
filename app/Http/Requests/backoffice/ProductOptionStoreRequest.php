<?php

namespace App\Http\Requests\backoffice;

use Illuminate\Foundation\Http\FormRequest;

class ProductOptionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_name' => ['required', 'string', 'max:255'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.value' => ['required', 'string', 'max:255'],
            'values.*.price_change' => ['nullable', 'numeric'],
        ];
    }
}
