<?php

namespace App\Http\Requests\Dashboard\Delegates;

use Illuminate\Foundation\Http\FormRequest;

class StoreDelegateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'document_number'  => ['nullable', 'string', 'max:100'],
            'issue_date'       => ['nullable', 'date', 'date_format:Y-m-d'],
            'expiry_date'      => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
