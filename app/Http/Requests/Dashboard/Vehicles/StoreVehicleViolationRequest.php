<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleViolationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warning_type_id' => ['nullable', 'integer', 'exists:warning_types,id'],
            'delegate_id'     => ['nullable', 'integer', 'exists:delegates,id'],
            'location'        => ['nullable', 'string', 'max:200'],
            'date'            => ['required', 'date', 'date_format:Y-m-d'],
            'amount'          => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ];
    }
}
