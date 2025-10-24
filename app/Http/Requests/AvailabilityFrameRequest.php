<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvailabilityFrameRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 如果有登录验证系统，可以改成只允许staff访问
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['nullable', 'date'],
            'day_of_week' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
            'duration' => ['required', 'integer', 'min:1'],
            'interval' => ['required', 'integer', 'min:0'],
            'is_recurring' => ['required', 'boolean'],
            'repeat_group_id' => ['nullable', 'uuid'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'staff_id is required',
            'staff_id.exists' => 'Invalid staff reference',
            'start_time.required' => 'Start time is required',
            'end_time.after' => 'End time must be after start time',
            'status.in' => 'Status must be either ACTIVE or INACTIVE',
        ];
    }
}
