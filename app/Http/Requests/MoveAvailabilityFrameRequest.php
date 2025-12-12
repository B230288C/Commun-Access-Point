<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveAvailabilityFrameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delta_minutes' => ['required', 'integer'],
            'new_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'delta_minutes.required' => 'Time delta in minutes is required',
            'delta_minutes.integer' => 'Time delta must be an integer (positive or negative)',
            'new_date.date' => 'New date must be a valid date format',
        ];
    }
}
