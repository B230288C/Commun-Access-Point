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
            'visitor_name'       => 'required|string|max:255',
            'nric_passport'      => 'required|string|max:50',
            'phone_number'       => 'required|string|max:20',
            'email'              => 'required|email|max:255',
            'purpose'            => 'required|string|max:255',
            'personal_in_charge' => 'required|string|max:255',
            'date'               => 'required|date|after_or_equal:today',
            'time'               => 'required|date_format:H:i',
            // status 在 Visitor 创建时不用传
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_name.required'        => 'Visitor name is required',
            'nric_passport.required'       => 'NRIC/Passport is required',
            'phone_number.required'        => 'Phone number is required',
            'email.required'               => 'Email is required',
            'email.email'                  => 'Email format is invalid',
            'purpose.required'             => 'Purpose is required',
            'personal_in_charge.required'  => 'Person in charge is required',
            'date.required'                => 'Appointment date is required',
            'date.after_or_equal'          => 'Appointment date cannot be in the past',
            'time.required'                => 'Appointment time is required',
            'time.date_format'             => 'Time must be in HH:MM format',
        ];
    }
}
