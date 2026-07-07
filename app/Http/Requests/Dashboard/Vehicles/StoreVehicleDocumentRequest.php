<?php

namespace App\Http\Requests\Dashboard\Vehicles;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'file'             => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'issue_date'       => ['nullable', 'date', 'date_format:Y-m-d'],
            'expiry_date'      => ['nullable', 'date', 'date_format:Y-m-d'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
