<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AppointmentStatus;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Visitor 提交表单，不需要登录
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name'                  => 'required|string|max:255',
            'nric_passport'                 => 'required|string|max:50',
            'phone_number'                  => 'required|string|max:20',
            'email'                         => 'required|email|max:255',
            'purpose'                       => 'required|string',
            'personal_in_charge'            => 'required|string|max:255',
            'date'                          => 'required|date|after_or_equal:today',
            'start_time'                    => 'required|date_format:H:i',
            'end_time'                      => 'required|date_format:H:i|after:start_time',
            'staff_id'                      => 'required|exists:staff,id',
            'availability_slot_id'          => 'required|exists:availability_slots,id',
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_name.required'         => 'Visitor name is required',
            'nric_passport.required'        => 'NRIC/Passport is required',
            'phone_number.required'         => 'Phone number is required',
            'email.required'                => 'Email is required',
            'email.email'                   => 'Email format is invalid',
            'purpose.required'              => 'Purpose is required',
            'personal_in_charge.required'   => 'Person in charge is required',
            'date.required'                 => 'Appointment date is required',
            'date.after_or_equal'           => 'Appointment date cannot be in the past',
            'start_time.required'           => 'Start time is required',
            'start_time.date_format'        => 'Start time must be in HH:MM format',
            'end_time.required'             => 'End time is required',
            'end_time.date_format'          => 'End time must be in HH:MM format',
            'end_time.after'                => 'End time must be after the start time',
            'staff_id.required'             => 'Staff member is required',
            'staff_id.exists'               => 'Selected staff does not exist',
            'availability_slot_id.required' => 'Slot is required',
            'availability_slot_id.exists'   => 'Selected slot does not exist',
        ];
    }
}
