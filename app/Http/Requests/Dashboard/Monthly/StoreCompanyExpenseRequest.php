<?php

namespace App\Http\Requests\Dashboard\Monthly;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'amount'   => ['required', 'numeric', 'min:0.01'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'الفئة مطلوبة.',
            'amount.required'   => 'المبلغ مطلوب.',
            'amount.min'        => 'يجب أن يكون المبلغ أكبر من صفر.',
        ];
    }
}
