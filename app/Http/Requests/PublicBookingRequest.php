<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name'         => 'required|string|max:255',
            'student_name'         => 'nullable|string|max:255',
            'phone_number'         => 'required|string|max:20',
            'email'                => 'required|email|max:255',
            'purpose'              => 'required|string|max:1000',
            'availability_slot_id' => 'required|exists:availability_slots,id',
            'staff_id'             => 'required|exists:staff,id',
            'date'                 => 'required|date|after_or_equal:today',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_name.required'         => 'Your name is required',
            'visitor_name.max'              => 'Name cannot exceed 255 characters',
            'student_name.max'              => 'Student name cannot exceed 255 characters',
            'phone_number.required'         => 'Phone number is required',
            'phone_number.max'              => 'Phone number cannot exceed 20 characters',
            'email.required'                => 'Email is required',
            'email.email'                   => 'Please enter a valid email address',
            'purpose.required'              => 'Purpose of visit is required',
            'purpose.max'                   => 'Purpose cannot exceed 1000 characters',
            'availability_slot_id.required' => 'Please select a time slot',
            'availability_slot_id.exists'   => 'Selected time slot is no longer available',
            'staff_id.required'             => 'Staff member is required',
            'staff_id.exists'               => 'Selected staff does not exist',
            'date.required'                 => 'Date is required',
            'date.after_or_equal'           => 'Appointment date cannot be in the past',
            'start_time.required'           => 'Start time is required',
            'end_time.required'             => 'End time is required',
        ];
    }
}
