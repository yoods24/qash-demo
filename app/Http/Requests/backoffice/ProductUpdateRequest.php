<?php

namespace App\Http\Requests\backoffice;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'alternate_name' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'goods_price' => ['nullable', 'numeric', 'min:0'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'product_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],
            'option_name' => ['nullable', 'string', 'max:255'],
            'values' => ['nullable', 'array'],
            'values.*.value' => ['nullable', 'string', 'max:255'],
            'values.*.price_change' => ['nullable', 'numeric'],
        ];
    }
}
