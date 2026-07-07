<?php

return [
    'required'  => 'حقل :attribute مطلوب.',
    'min'       => [
        'string' => 'يجب أن يتكون :attribute من :min أحرف على الأقل.',
    ],
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'current_password' => 'كلمة المرور الحالية غير صحيحة.',

    'attributes' => [
        'delegate_code'       => 'رقم المندوب',
        'password'            => 'كلمة المرور',
        'current_password'    => 'كلمة المرور الحالية',
        'new_password'        => 'كلمة المرور الجديدة',
        'new_password_confirmation' => 'تأكيد كلمة المرور',
    ],
];
