<?php

namespace App\Http\Requests\Dashboard\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100'],
            'min_km_threshold' => ['required', 'numeric', 'min:0'],
            'penalty_per_km'   => ['required', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
        ];
    }
}
