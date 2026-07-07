<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvanceEntryRequest extends FormRequest
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
            'amount'            => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'entry_date'        => ['nullable', 'date', 'date_format:Y-m-d'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}
