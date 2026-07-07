<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class SyncCompanyDeductionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deductions'                          => ['required', 'array'],
            'deductions.*.deduction_category_id'  => ['nullable', 'integer', 'exists:deduction_categories,id'],
            'deductions.*.amount'                 => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'deductions.*.reason'                 => ['nullable', 'string', 'max:255'],
            'deductions.*.notes'                  => ['nullable', 'string'],
        ];
    }
}
