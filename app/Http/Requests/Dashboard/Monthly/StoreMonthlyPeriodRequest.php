<?php

namespace App\Http\Requests\Dashboard\Monthly;

use App\Models\MonthlyPeriod;
use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform_id' => ['required', 'integer', 'exists:platforms,id'],
            'year'        => ['required', 'integer', 'between:2024,2030'],
            'month'       => ['required', 'integer', 'between:1,12'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $platformId = (int) $this->input('platform_id');
            $year       = (int) $this->input('year');
            $month      = (int) $this->input('month');

            if ($platformId && $year && $month) {
                $exists = MonthlyPeriod::where('platform_id', $platformId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->exists();

                if ($exists) {
                    $label = MonthlyPeriod::makeLabel($month, $year);
                    $validator->errors()->add('month', "فترة {$label} موجودة بالفعل لهذه المنصة.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'platform_id.required' => 'يجب اختيار المنصة.',
            'platform_id.exists'   => 'المنصة المختارة غير موجودة.',
            'year.required'        => 'يجب تحديد السنة.',
            'year.between'         => 'السنة يجب أن تكون بين 2024 و 2030.',
            'month.required'       => 'يجب تحديد الشهر.',
            'month.between'        => 'الشهر يجب أن يكون بين 1 و 12.',
        ];
    }
}
