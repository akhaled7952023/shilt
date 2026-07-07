<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'        => ['required', 'date', 'date_format:Y-m-d'],
            'description' => ['required', 'string'],
            'cost'        => ['nullable', 'numeric', 'min:0'],
            'status'      => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'notes'       => ['nullable', 'string'],
        ];
    }
}
