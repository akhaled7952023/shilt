<?php

namespace App\Http\Requests\Dashboard\MasterData;

use App\Enums\DocumentAppliesTo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'array'],
            'name.ar'     => ['required', 'string', 'max:255'],
            'name.en'     => ['nullable', 'string', 'max:255'],
            'applies_to'  => ['required', Rule::enum(DocumentAppliesTo::class)],
            'is_required' => ['boolean'],
            'is_active'   => ['boolean'],
        ];
    }
}
