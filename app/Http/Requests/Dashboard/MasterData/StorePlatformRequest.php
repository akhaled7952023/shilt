<?php

namespace App\Http\Requests\Dashboard\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100', Rule::unique('platforms', 'name')],
            'code'             => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('platforms', 'code')],
            'min_km_threshold' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'penalty_per_km'   => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'is_active'        => ['boolean'],
        ];
    }
}
