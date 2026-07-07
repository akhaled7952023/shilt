<?php

namespace App\Http\Requests\Dashboard\Delegates;

use App\Models\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDelegateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $code = Platform::find($this->input('platform_id'))?->code;

        if ($code === 'hungerstation' && $this->filled('hungerstation_rider_id')) {
            $this->merge(['delegate_code' => $this->input('hungerstation_rider_id')]);
        } elseif ($code === 'the-chefz' && $this->filled('national_id')) {
            $this->merge(['delegate_code' => $this->input('national_id')]);
        }
    }

    public function rules(): array
    {
        $isHs = Platform::find($this->input('platform_id'))?->code === 'hungerstation';

        return [
            'delegate_code'           => ['required', 'string', 'max:20', Rule::unique('delegates', 'delegate_code')],
            'name'                    => ['required', 'string', 'max:150'],
            'national_id'             => ['nullable', 'string', 'max:20', Rule::unique('delegates', 'national_id')->where('platform_id', $this->input('platform_id'))],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'city_id'                 => ['nullable', 'integer', 'exists:cities,id'],
            'platform_id'             => ['nullable', 'integer', 'exists:platforms,id'],
            'hungerstation_rider_id'  => array_filter([
                $isHs ? 'required' : 'nullable',
                'string',
                'max:20',
                Rule::unique('delegates', 'hungerstation_rider_id'),
            ]),
            'bank_name'               => ['nullable', 'string', 'max:150'],
            'iban'                    => ['nullable', 'string', 'max:34'],
            'profile_photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'iqama_image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driving_license_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'hungerstation_rider_id.required' => 'Rider ID مطلوب عند اختيار منصة هنقرستيشن.',
            'hungerstation_rider_id.unique'   => 'هذا الـ Rider ID مسجل لمندوب آخر.',
        ];
    }
}
