<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarningEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delegate_id'       => ['required', 'integer', 'exists:delegates,id'],
            'monthly_period_id' => ['nullable', 'integer', 'exists:monthly_periods,id'],
            'warning_type_id'   => ['nullable', 'integer', 'exists:warning_types,id'],
            'description'       => ['required', 'string'],
            'warning_date'      => ['nullable', 'date', 'date_format:Y-m-d'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}
