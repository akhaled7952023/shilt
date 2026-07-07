<?php

namespace App\Http\Requests\Dashboard\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings'         => ['required', 'array'],
            'settings.*.key'   => ['required', 'string', 'max:100'],
            'settings.*.value' => ['nullable', 'string'],
        ];
    }
}
