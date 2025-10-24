<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name' => 'sometimes|string|max:255',
            'nric_passport' => 'sometimes|string|max:50',
            'phone_number' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'purpose' => 'sometimes|string|max:255',
            'personal_in_charge' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'staff_id' => 'sometimes|exists:staff,id',
            'status' => 'sometimes|in:pending,approved,cancelled,completed',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.exists' => 'Selected staff does not exist',
            'status.in' => 'Invalid status',
        ];
    }
}
