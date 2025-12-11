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
            'start_time' => ['required', 'date_format:H:i:s,H:i'],
            'end_time' => ['required', 'date_format:H:i:s,H:i', 'after:start_time'],
            'duration' => ['required', 'integer', 'min:1'],
            'interval' => ['required', 'integer', 'min:0'],
            'is_recurring' => ['required', 'boolean'],
            'repeat_group_id' => ['nullable', 'uuid'],
            'status' => ['required', 'in:active,inactive'],
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'staff_id is required',
            'staff_id.exists' => 'Invalid staff reference',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM or HH:MM:SS format',
            'end_time.required' => 'End time is required',
            'end_time.date_format' => 'End time must be in HH:MM or HH:MM:SS format',
            'end_time.after' => 'End time must be after start time',
            'status.in' => 'Status must be either Active or Inactive',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize time format to include seconds
        if ($this->has('start_time') && strlen($this->start_time) === 5) {
            $this->merge(['start_time' => $this->start_time . ':00']);
        }

        if ($this->has('end_time') && strlen($this->end_time) === 5) {
            $this->merge(['end_time' => $this->end_time . ':00']);
        }
    }
}
