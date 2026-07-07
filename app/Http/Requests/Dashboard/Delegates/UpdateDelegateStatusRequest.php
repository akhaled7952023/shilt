<?php

namespace App\Http\Requests\Dashboard\Delegates;

use App\Enums\DelegateStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDelegateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DelegateStatus::class)],
        ];
    }
}
