<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class StoreViolationEntryRequest extends FormRequest
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
            'description'       => ['required', 'string'],
            'amount'            => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'violation_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}
