<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delegate_id'       => ['required', 'integer', 'exists:delegates,id'],
            'monthly_period_id' => ['required', 'integer', 'exists:monthly_periods,id'],
            'leave_type_id'     => ['required', 'integer', 'exists:leave_types,id'],
            'start_date'        => ['required', 'date', 'date_format:Y-m-d'],
            'end_date'          => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'days_count'        => ['nullable', 'integer', 'min:0'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}
