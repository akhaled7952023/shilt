<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plate_number'             => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->whereNull('deleted_at')],
            'vehicle_type_id'          => ['nullable', 'integer', 'exists:vehicle_types,id'],
            'make'                     => ['required', 'string', 'max:100'],
            'model'                    => ['required', 'string', 'max:100'],
            'year'                     => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'color'                    => ['nullable', 'string', 'max:50'],
            'chassis_number'           => ['nullable', 'string', 'max:100'],
            'status'                   => ['nullable', Rule::in(['available', 'maintenance'])],
            'notes'                    => ['nullable', 'string'],
            'vehicle_image'            => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'registration_image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'insurance_image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'registration_number'      => ['nullable', 'string', 'max:50'],
            'registration_issue_date'  => ['nullable', 'date', 'date_format:Y-m-d'],
            'registration_expiry_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'insurance_company'        => ['nullable', 'string', 'max:150'],
            'insurance_policy_number'  => ['nullable', 'string', 'max:50'],
            'insurance_start_date'     => ['nullable', 'date', 'date_format:Y-m-d'],
            'insurance_expiry_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'inspection_number'        => ['nullable', 'string', 'max:50'],
            'inspection_issue_date'    => ['nullable', 'date', 'date_format:Y-m-d'],
            'inspection_expiry_date'   => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
