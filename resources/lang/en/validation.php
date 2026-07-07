<?php

return [
    'required'  => 'The :attribute field is required.',
    'min'       => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The current password is incorrect.',

    'attributes' => [
        'delegate_code'       => 'delegate ID',
        'password'            => 'password',
        'current_password'    => 'current password',
        'new_password'        => 'new password',
        'new_password_confirmation' => 'password confirmation',
    ],
];
