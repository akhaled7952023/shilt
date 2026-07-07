<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use App\Enums\PaymentBy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delegate_id'       => ['nullable', 'integer', 'exists:delegates,id'],
            'monthly_period_id' => ['required', 'integer', 'exists:monthly_periods,id'],
            'payment_by'        => ['required', Rule::enum(PaymentBy::class)],
            'amount'            => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}
