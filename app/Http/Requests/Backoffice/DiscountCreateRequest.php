<?php

namespace App\Http\Requests\backoffice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountCreateRequest extends FormRequest
{
    protected ?string $tenantContext = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->tenantContext = $this->resolveTenantId();

        if ($this->tenantContext) {
            $this->merge([
                'tenant_id' => $this->tenantContext,
            ]);
        }
    }

    public function rules(): array
    {
        $dayRules = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $productRule = Rule::exists('products', 'id');

        if ($this->tenantContext) {
            $productRule = $productRule->where('tenant_id', $this->tenantContext);
        }

        return [
            'tenant_id' => ['required', 'string', Rule::exists('tenants', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in(['flat', 'percent'])],
            'value' => ['required', 'numeric', 'min:0'],
            'applicable_for' => ['required', Rule::in(['all', 'specific'])],
            'products' => ['required_if:applicable_for,specific', 'array'],
            'products.*' => [
                'integer',
                $productRule,
            ],
            'valid_from' => ['required', 'date'],
            'valid_till' => ['required', 'date', 'after_or_equal:valid_from'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['string', Rule::in($dayRules)],
            'quantity_type' => ['required', Rule::in(['unlimited', 'decrement'])],
            'quantity' => ['required_if:quantity_type,decrement', 'nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function resolveTenantId(): ?string
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;

        if (! $tenantId && $this->user()) {
            $tenantId = $this->user()->tenant_id;
        }

        return $tenantId;
    }
}
