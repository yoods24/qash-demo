<?php

declare(strict_types=1);

namespace App\Http\Requests\backoffice\TenantProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_heading' => ['required', 'string', 'max:255'],
            'brand_slogan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
