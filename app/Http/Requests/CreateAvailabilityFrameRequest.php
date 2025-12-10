<?php

namespace App\Http\Requests;

use App\Enums\AvailabilityFrameStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAvailabilityFrameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
            'date' => [
                Rule::requiredIf(function () {
                    return !$this->boolean('is_recurring');
                }),
                'nullable',
                'date',
                'date_format:Y-m-d',
            ],
            'start_time' => ['required', 'date_format:H:i:s,H:i'],
            'end_time' => ['required', 'date_format:H:i:s,H:i', 'after:start_time'],
            'title' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'integer', 'min:5'],
            'interval' => ['nullable', 'integer', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'day' => [
                'nullable',
                'string',
                Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            ],
            'status' => ['nullable', 'string', Rule::in([AvailabilityFrameStatus::Active->value, AvailabilityFrameStatus::Inactive->value])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'staff_id.required' => 'Staff ID is required.',
            'staff_id.exists' => 'The selected staff does not exist.',
            'date.required' => 'Date is required for non-recurring frames.',
            'start_time.required' => 'Start time is required.',
            'end_time.after' => 'End time must be after start time.',
            'title.required' => 'Title is required.',
            'duration.required' => 'Duration is required.',
            'duration.min' => 'Duration must be at least 5 minutes.',
            'interval.min' => 'Interval cannot be negative.',
            'day.in' => 'Invalid day.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize time format to include seconds
        if ($this->has('start_time') && strlen($this->start_time) === 5) {
            $this->merge(['start_time' => $this->start_time.':00']);
        }

        if ($this->has('end_time') && strlen($this->end_time) === 5) {
            $this->merge(['end_time' => $this->end_time.':00']);
        }

        // Set default values
        $this->merge([
            'interval' => $this->input('interval', 0),
            'is_recurring' => $this->boolean('is_recurring', false),
            'status' => $this->input('status', AvailabilityFrameStatus::Active->value),
        ]);
    }
}
