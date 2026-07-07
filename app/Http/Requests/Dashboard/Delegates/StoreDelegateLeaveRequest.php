<?php

namespace App\Http\Requests\Dashboard\Delegates;

use Illuminate\Foundation\Http\FormRequest;

class StoreDelegateLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date'    => ['required', 'date', 'date_format:Y-m-d'],
            'end_date'      => ['required', 'date', 'date_format:Y-m-d', 'gte:start_date'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'نوع الإجازة مطلوب',
            'start_date.required'    => 'تاريخ البداية مطلوب',
            'end_date.required'      => 'تاريخ النهاية مطلوب',
            'end_date.gte'           => 'تاريخ النهاية يجب أن يكون مساوياً أو بعد تاريخ البداية',
        ];
    }
}
