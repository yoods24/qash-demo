<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Event;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_type' => ['required', Rule::in(Event::EVENT_TYPES)],
            'use_date_range' => ['nullable', 'boolean'],
            'event_date' => ['required_without:use_date_range', 'nullable', 'date'],
            'date_from' => ['required_if:use_date_range,1', 'nullable', 'date'],
            'date_till' => ['required_if:use_date_range,1', 'nullable', 'date', 'after_or_equal:date_from'],
            'location' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'event_highlights' => ['nullable', 'string'],
            'what_to_expect' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'capacity_unlimited' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
