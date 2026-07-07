<?php

namespace App\Http\Requests\Dashboard\Settings;

use App\Models\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $key     = $this->route('key');
        $setting = SystemSetting::where('key', $key)->first();

        if (! $setting) {
            return ['value' => ['nullable', 'string', 'max:500']];
        }

        // Logo is handled as a file upload, not a text value
        if ($key === 'company_logo_path') {
            return [
                'value_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            ];
        }

        $rules = match ($setting->type?->value) {
            'decimal' => ['required', 'numeric', 'min:0'],
            'integer' => ['required', 'integer', 'min:0'],
            'boolean' => ['required', 'in:true,false,1,0'],
            default   => ['required', 'string', 'max:500'],
        };

        return ['value' => $rules];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'قيمة الإعداد مطلوبة.',
            'value.numeric'  => 'يجب أن تكون القيمة رقماً.',
            'value.integer'  => 'يجب أن تكون القيمة رقماً صحيحاً.',
            'value.min'      => 'يجب أن تكون القيمة أكبر من أو تساوي صفر.',
            'value.string'   => 'يجب أن تكون القيمة نصاً.',
            'value.max'      => 'يجب ألا تتجاوز القيمة 500 حرف.',
            'value.in'       => 'القيمة المنطقية يجب أن تكون true أو false.',
            'value_file.image'  => 'يجب أن يكون الملف صورة.',
            'value_file.mimes'  => 'الصيغ المسموحة: png, jpg, jpeg.',
            'value_file.max'    => 'الحد الأقصى لحجم الصورة 2 ميجابايت.',
        ];
    }
}
