<?php

namespace App\Http\Requests\Dashboard\Monthly;

use App\Enums\PeriodAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::enum(PeriodAction::class)],
            'reason' => ['nullable', 'string', 'max:1000', 'required_if:action,reopened'],
        ];
    }
}
