<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class ReturnVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'returned_at' => ['required', 'date', 'date_format:Y-m-d'],
            'notes'       => ['nullable', 'string'],
        ];
    }
}
