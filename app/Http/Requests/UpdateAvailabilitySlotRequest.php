<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'status' => 'sometimes|in:available,booked,unavailable'
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.date_format' => 'Start time must be in HH:mm format.',
            'end_time.date_format' => 'End time must be in HH:mm format.',
            'end_time.after' => 'End time must be after start time.',
            'status.in' => 'Status must be available, booked, or unavailable.',
        ];
    }
}
