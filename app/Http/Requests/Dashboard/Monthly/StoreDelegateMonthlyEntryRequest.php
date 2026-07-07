<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class StoreDelegateMonthlyEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delegate_id'            => ['required', 'integer', 'exists:delegates,id'],
            'platform_id'            => ['required', 'integer', 'exists:platforms,id'],
            'orders_count'           => ['nullable', 'integer', 'min:0'],
            'eligible_km'            => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'distance_pay'           => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'tips'                   => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'grants'                 => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'housing_allowance'      => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'bonus'                  => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'fuel_deduction'         => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'wallet_deduction'       => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'app_deduction'          => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'previous_carry_over'    => ['nullable', 'numeric', 'max:9999999.99'],
            'short_distance_penalty' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'notes'                  => ['nullable', 'string'],
        ];
    }
}
