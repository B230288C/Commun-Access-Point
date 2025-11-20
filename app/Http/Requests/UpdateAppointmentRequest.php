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
            'visitor_name'                 => 'sometimes|string|max:255',
            'nric_passport'                => 'sometimes|string|max:50',
            'phone_number'                 => 'sometimes|string|max:20',
            'email'                        => 'sometimes|email|max:255',
            'purpose'                      => 'sometimes|string',
            'personal_in_charge'           => 'sometimes|string|max:255',
            'date'                         => 'sometimes|date',
            'start_time'                   => 'sometimes|date_format:H:i',
            'end_time'                     => 'sometimes|date_format:H:i',
            'staff_id'                     => 'sometimes|exists:staff,id',
            'status'                       => 'sometimes|in:pending,approved,cancelled,completed',
            'availability_slot_id'         => 'sometimes|exists:availability_slots,id',
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_name.max'             => 'Visitor name cannot exceed 255 characters',
            'nric_passport.max'            => 'NRIC/Passport cannot exceed 50 characters',
            'phone_number.max'             => 'Phone number cannot exceed 20 characters',
            'email.email'                  => 'Email format is invalid',
            'email.max'                    => 'Email cannot exceed 255 characters',
            'purpose.string'               => 'Purpose must be a string',
            'personal_in_charge.max'       => 'Person in charge cannot exceed 255 characters',
            'date.date'                    => 'Invalid date format',
            'date.after_or_equal'          => 'Appointment date cannot be in the past',
            'start_time.date_format'       => 'Start time must be in HH:MM format',
            'end_time.date_format'         => 'End time must be in HH:MM format',
            'end_time.after'               => 'End time must be after start time',
            'staff_id.exists'              => 'Selected staff does not exist',
            'availability_slot_id.exists'  => 'Selected slot does not exist',
            'status.in'                    => 'Invalid status',
        ];
    }
}
