<?php

namespace App\Http\Requests\Delegate;

use Illuminate\Foundation\Http\FormRequest;

class DelegateLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ];
    }
}
