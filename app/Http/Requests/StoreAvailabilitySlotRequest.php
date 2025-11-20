<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilitySlotRequest extends FormRequest
{
    /**
     * Authorize the request.
     */
    public function authorize(): bool
    {
        return true; // Add permission logic if needed
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:mm format.',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:mm format.',
            'end_time.after' => 'End time must be after start time.',
        ];
    }
}
