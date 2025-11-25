<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'emp_code' => ['nullable', 'string', 'max:120', 'unique:users,emp_code'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'joining_date' => ['nullable', 'date'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'blood_group' => ['nullable', 'in:O,A,B,AB'],
            'about' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'zipcode' => ['nullable', 'string', 'max:30'],
            'emergency_contact_number_1' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation_1' => ['nullable', 'string', 'max:60'],
            'emergency_contact_name_1' => ['nullable', 'string', 'max:120'],
            'emergency_contact_number_2' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation_2' => ['nullable', 'string', 'max:60'],
            'emergency_contact_name_2' => ['nullable', 'string', 'max:120'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'exists:roles,name'],
            'profile-image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
