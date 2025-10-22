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
        // 允许任何人通过授权（或者根据你的逻辑改成判断权限）
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visitor_name' => 'sometimes|string|max:255',
            'nric_passport' => 'sometimes|string|max:20',
            'phone_number' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'purpose' => 'sometimes|string|max:255',
            'person_in_charge' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes',
            'end_time' => 'sometimes',
            // 注意：status 不在这里，不允许直接通过请求修改
        ];
    }
}
