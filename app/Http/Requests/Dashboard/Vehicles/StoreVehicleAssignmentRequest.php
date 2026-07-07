<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delegate_id' => ['required', 'integer', 'exists:delegates,id'],
            'assigned_at' => ['required', 'date', 'date_format:Y-m-d'],
            'notes'       => ['nullable', 'string'],
        ];
    }
}
