<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ' :attribute field must be accepted.',
    'accepted_if' => ' :attribute field must be accepted when :other is :value.',
    'active_url' => ' :attribute field must be a valid URL.',
    'after' => ' :attribute field must be a date after :date.',
    'after_or_equal' => ' :attribute field must be a date after or equal to :date.',
    'alpha' => ' :attribute field must only contain letters.',
    'alpha_dash' => ' :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_num' => ' :attribute field must only contain letters and numbers.',
    'array' => ' :attribute field must be an array.',
    'ascii' => ' :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => ' :attribute field must be a date before :date.',
    'before_or_equal' => ' :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => ' :attribute field must have between :min and :max items.',
        'file' => ' :attribute field must be between :min and :max kilobytes.',
        'numeric' => ' :attribute field must be between :min and :max.',
        'string' => ' :attribute field must be between :min and :max characters.',
    ],
    'boolean' => ' :attribute field must be true or false.',
    'unique_translation_rule' => 'The :attribute has already been taken.',
    'can' => ' :attribute field contains an unauthorized value.',
    'confirmed' => ' :attribute field confirmation does not match.',
    'contains' => ' :attribute field is missing a required value.',
    'current_password' => ' password is incorrect.',
    'date' => ' :attribute field must be a valid date.',
    'date_equals' => ' :attribute field must be a date equal to :date.',
    'date_format' => ' :attribute field must match the format :format.',
    'decimal' => ' :attribute field must have :decimal decimal places.',
    'declined' => ' :attribute field must be declined.',
    'declined_if' => ' :attribute field must be declined when :other is :value.',
    'different' => ' :attribute field and :other must be different.',
    'digits' => ' :attribute field must be :digits digits.',
    'digits_between' => ' :attribute field must be between :min and :max digits.',
    'dimensions' => ' :attribute field has invalid image dimensions.',
    'distinct' => ' :attribute field has a duplicate value.',
    'doesnt_end_with' => ' :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => ' :attribute field must not start with one of the following: :values.',
    'email' => ' :attribute field must be a valid email address.',
    'ends_with' => ' :attribute field must end with one of the following: :values.',
    'enum' => ' selected :attribute is invalid.',
    'exists' => ' selected :attribute is invalid.',
    'extensions' => ' :attribute field must have one of the following extensions: :values.',
    'file' => ' :attribute field must be a file.',
    'filled' => ' :attribute field must have a value.',
    'gt' => [
        'array' => ' :attribute field must have more than :value items.',
        'file' => ' :attribute field must be greater than :value kilobytes.',
        'numeric' => ' :attribute field must be greater than :value.',
        'string' => ' :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => ' :attribute field must have :value items or more.',
        'file' => ' :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => ' :attribute field must be greater than or equal to :value.',
        'string' => ' :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => ' :attribute field must be a valid hexadecimal color.',
    'image' => ' :attribute field must be an image.',
    'in' => ' selected :attribute is invalid.',
    'in_array' => ' :attribute field must exist in :other.',
    'integer' => ' :attribute field must be an integer.',
    'ip' => ' :attribute field must be a valid IP address.',
    'ipv4' => ' :attribute field must be a valid IPv4 address.',
    'ipv6' => ' :attribute field must be a valid IPv6 address.',
    'json' => ' :attribute field must be a valid JSON string.',
    'list' => ' :attribute field must be a list.',
    'lowercase' => ' :attribute field must be lowercase.',
    'lt' => [
        'array' => ' :attribute field must have less than :value items.',
        'file' => ' :attribute field must be less than :value kilobytes.',
        'numeric' => ' :attribute field must be less than :value.',
        'string' => ' :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => ' :attribute field must not have more than :value items.',
        'file' => ' :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => ' :attribute field must be less than or equal to :value.',
        'string' => ' :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => ' :attribute field must be a valid MAC address.',
    'max' => [
        'array' => ' :attribute field must not have more than :max items.',
        'file' => ' :attribute field must not be greater than :max kilobytes.',
        'numeric' => ' :attribute field must not be greater than :max.',
        'string' => ' :attribute field must not be greater than :max characters.',
    ],
    'max_digits' => ' :attribute field must not have more than :max digits.',
    'mimes' => ' :attribute field must be a file of type: :values.',
    'mimetypes' => ' :attribute field must be a file of type: :values.',
    'min' => [
        'array' => ' :attribute field must have at least :min items.',
        'file' => ' :attribute field must be at least :min kilobytes.',
        'numeric' => ' :attribute field must be at least :min.',
        'string' => ' :attribute field must be at least :min characters.',
    ],
    'min_digits' => ' :attribute field must have at least :min digits.',
    'missing' => ' :attribute field must be missing.',
    'missing_if' => ' :attribute field must be missing when :other is :value.',
    'missing_unless' => ' :attribute field must be missing unless :other is :value.',
    'missing_with' => ' :attribute field must be missing when :values is present.',
    'missing_with_all' => ' :attribute field must be missing when :values are present.',
    'multiple_of' => ' :attribute field must be a multiple of :value.',
    'not_in' => ' selected :attribute is invalid.',
    'not_regex' => ' :attribute field format is invalid.',
    'numeric' => ' :attribute field must be a number.',
    'password' => [
        'letters' => ' :attribute field must contain at least one letter.',
        'mixed' => ' :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => ' :attribute field must contain at least one number.',
        'symbols' => ' :attribute field must contain at least one symbol.',
        'uncompromised' => ' given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => ' :attribute field must be present.',
    'present_if' => ' :attribute field must be present when :other is :value.',
    'present_unless' => ' :attribute field must be present unless :other is :value.',
    'present_with' => ' :attribute field must be present when :values is present.',
    'present_with_all' => ' :attribute field must be present when :values are present.',
    'prohibited' => ' :attribute field is prohibited.',
    'prohibited_if' => ' :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => ' :attribute field is prohibited unless :other is in :values.',
    'prohibits' => ' :attribute field prohibits :other from being present.',
    'regex' => ' :attribute field format is invalid.',
    'required' => ' :attribute field is required.',
    'required_array_keys' => ' :attribute field must contain entries for: :values.',
    'required_if' => ' :attribute field is required when :other is :value.',
    'required_if_accepted' => ' :attribute field is required when :other is accepted.',
    'required_if_declined' => ' :attribute field is required when :other is declined.',
    'required_unless' => ' :attribute field is required unless :other is in :values.',
    'required_with' => ' :attribute field is required when :values is present.',
    'required_with_all' => ' :attribute field is required when :values are present.',
    'required_without' => ' :attribute field is required when :values is not present.',
    'required_without_all' => ' :attribute field is required when none of :values are present.',
    'same' => ' :attribute field must match :other.',
    'size' => [
        'array' => ' :attribute field must contain :size items.',
        'file' => ' :attribute field must be :size kilobytes.',
        'numeric' => ' :attribute field must be :size.',
        'string' => ' :attribute field must be :size characters.',
    ],
    'starts_with' => ' :attribute field must start with one of the following: :values.',
    'string' => ' :attribute field must be a string.',
    'timezone' => ' :attribute field must be a valid timezone.',
    'unique' => ' :attribute has already been taken.',
    'uploaded' => ' :attribute failed to upload.',
    'uppercase' => ' :attribute field must be uppercase.',
    'url' => ' :attribute field must be a valid URL.',
    'ulid' => ' :attribute field must be a valid ULID.',
    'uuid' => ' :attribute field must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'g-recaptcha-response' => [
            'required' => 'you should first check you are not a robot',
        ],
        'email' => [
            'required' => 'email is required',
        ],
        'password' => [
            'required' => 'password is required',
        ],
        'name' => [
            'required' => 'name is required',
        ],
        'role_id' => [
            'required' => 'role is required',
        ],
        'code' => [
            'required' => 'otp is required',
            'min' => 'Otp must have 5 numbers at least',
        ],
        'mainemail' => [
            'required' => 'The main email is required.',
        ],
        'logo' => [
            'required' => 'The logo is required.',
        ],
        'phonenumber' => [
            'required' => 'The phone number is required.',
        ],
        'adress_ar' => [
            'required' => 'Address in Arabic is required.',
        ],
        'about_ar' => [
            'required' => 'About Us in Arabic is required.',
        ],
        'adress_en' => [
            'required' => 'Address in English is required.',
        ],
        'about_en' => [
            'required' => 'About Us in English is required.',
        ],

        'social_links' => [
            'required' => 'The social media accounts are required.',
        ],
        'social_links.*.link' => [
            'required' => 'The link for account is required.',
            'url' => 'The link for account  must be a valid URL.',
        ],
        'social_links.*.icon' => [
            'required' => 'The icon for account  is required.',
            'string' => 'The icon for account  must be a string.',
        ],
        'clients.*.link' => [
            'required' => 'The link for client is required.',
            'url' => 'The link for client number must be a valid URL.',
        ],
        'clients.*.icon' => [
            'required' => 'The icon for client is required.',
        ],

        'title.ar' => [
            'required' => 'Title in Arabic is required',
            'string' => 'Title in Arabic must be a string',
            'max' => 'Title in Arabic cannot exceed 255 characters',
        ],
        'title.en' => [
            'required' => 'Title in English is required',
            'string' => 'Title in English must be a string',
            'max' => 'Title in English cannot exceed 255 characters',
        ],
        'slug.ar' => [
            'required' => 'Slug in Arabic is required',
            'string' => 'Slug in Arabic must be a string',
            'max' => 'Slug in Arabic cannot exceed 255 characters',
        ],
        'slug.en' => [
            'required' => 'Slug in English is required',
            'string' => 'Slug in English must be a string',
            'max' => 'Slug in English cannot exceed 255 characters',
        ],
        'description_ar' => [
            'required' => 'Description in Arabic is required',
            'string' => 'Description in Arabic must be a string',
        ],
        'description_en' => [
            'required' => 'Description in English is required',
            'string' => 'Description in English must be a string',
        ],
        'meta_description_ar' => [
            'required' => 'Meta description in Arabic is required',
            'string' => 'Meta description in Arabic must be a string',
            'max' => 'Meta description in Arabic cannot exceed 170 characters',
        ],
        'meta_description_en' => [
            'required' => 'Meta description in English is required',
            'string' => 'Meta description in English must be a string',
            'max' => 'Meta description in English cannot exceed 170 characters',
        ],
        'meta_keywords_ar' => [
            'required' => 'Meta keywords in Arabic are required',
            'string' => 'Meta keywords in Arabic must be a string',
            'max' => 'Meta keywords in Arabic cannot exceed 1000 characters',
        ],
        'meta_keywords_en' => [
            'required' => 'Meta keywords in English are required',
            'string' => 'Meta keywords in English must be a string',
            'max' => 'Meta keywords in English cannot exceed 1000 characters',
        ],
        'meta_title_ar' => [
            'required' => 'Meta title in Arabic is required',
            'string' => 'Meta title in Arabic must be a string',
            'max' => 'Meta title in Arabic cannot exceed 70 characters',
        ],
        'meta_title_en' => [
            'required' => 'Meta title in English is required',
            'string' => 'Meta title in English must be a string',
            'max' => 'Meta title in English cannot exceed 70 characters',
        ],
        'image' => [
            'required' => 'Image is required',
            'image' => 'The file must be an image',
            'mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg',
            'max' => 'The image size cannot exceed 2MB',
        ],
        'status' => [
            'required' => 'Status is required',
            'boolean' => 'Status must be a boolean value (true or false)',
        ],

        'categories.*.ar' => [
            'required' => 'The Arabic category name is required.',
            'string' => 'The Arabic category name must be a valid string.',
            'min' => 'The Arabic category name must be at least :min characters.',
            'max' => 'The Arabic category name may not be greater than :max characters.',
        ],

        'categories.*.en' => [
            'required' => 'The English category name is required.',
            'string' => 'The English category name must be a valid string.',
            'min' => 'The English category name must be at least :min characters.',
            'max' => 'The English category name may not be greater than :max characters.',
        ],

        'categories.*.order' => [
            'required' => 'The order field is required.',
            'integer' => 'The order must be an integer.',
            'min' => 'The order value must not be less than :min.',
        ],

        'categories.*.status' => [
            'required' => 'The status field is required.',
            'in' => 'The status must be either active or inactive.',
        ],

        'categories.*.id' => [
            'integer' => 'The ID must be a valid integer.',
            'exists' => 'The selected category does not exist.',
        ],
        'firstsolgan_ar' => [
            'required' => 'The first slogan in Arabic is required.',
            'max' => 'The first Arabic slogan must not exceed :max characters.',
        ],

        'firstsolgan_en' => [
            'required' => 'The first slogan in English is required.',
            'max' => 'The first English slogan must not exceed :max characters.',
        ],

        'secondsolgan_ar' => [
            'required' => 'The second slogan in Arabic is required.',
            'max' => 'The second Arabic slogan must not exceed :max characters.',
        ],

        'secondsolgan_en' => [
            'required' => 'The second slogan in English is required.',
            'max' => 'The second English slogan must not exceed :max characters.',
        ],

        'textbutton_ar' => [
            'required' => 'The button text in Arabic is required.',
        ],

        'textbutton_en' => [
            'required' => 'The button text in English is required.',
        ],

        'link' => [
            'required' => 'The link is required.',
            'url' => 'The link must be a valid URL.',
            'max' => 'The link must not exceed :max characters.',
        ],

        'features.*.text_ar' => [
            'required' => 'The Arabic text for each feature is required.',
            'string' => 'The Arabic text must be a valid string.',
            'max' => 'The Arabic text must not exceed :max characters.',
        ],

        'features.*.text_en' => [
            'required' => 'The English text for each feature is required.',
            'string' => 'The English text must be a valid string.',
            'max' => 'The English text must not exceed :max characters.',
        ],

        'features.*.image' => [
            'image' => 'The feature image must be a valid image file.',
            'mimes' => 'The feature image must be of type: jpeg, png, jpg, gif, svg.',
            'max' => 'The feature image size must not exceed :max kilobytes.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'name',
        'username' => 'اسم المُستخدم',
        'email' => 'email',
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'password' => 'كلمة السر',
        'password_confirmation' => 'تأكيد كلمة السر',
        'city' => 'المدينة',
        'country' => 'الدولة',
        'address' => 'عنوان السكن',
        'phone' => 'الهاتف',
        'mobile' => 'الجوال',
        'age' => 'العمر',
        'sex' => 'الجنس',
        'gender' => 'النوع',
        'day' => 'اليوم',
        'month' => 'الشهر',
        'year' => 'السنة',
        'hour' => 'ساعة',
        'minute' => 'دقيقة',
        'second' => 'ثانية',
        'title' => 'العنوان',
        'content' => 'المُحتوى',
        'description' => 'الوصف',
        'excerpt' => 'المُلخص',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'available' => 'مُتاح',
        'size' => 'الحجم',
        'color' => 'اللون',
        'quantity' => 'الكميه',
        'descount' => 'الخصم',
        'price' => 'السعر',
        'category_id' => 'القسم',
        'minimum_withdrawal_amount' => 'الحد الادنى  للسحب',
        'maximum_withdrawal_amount' => 'الحد الاقصى للسحب ',
        'the_lowest_amount_in_the_account' => 'الحد الادنى للمبلغ المتبقي في الحساب',
        'method ' => 'وسيله الدفع',
        'governorate_id' => 'المحافظه',
        'city_id' => 'المدينه',
        'health_certificate' => 'الشهاده الصحيه',
        'terms' => 'الموافقه علي الشروط مطلوبه',
        'otp' => 'رمز التحقق',
        'method' => 'وسيله السحب',
        'body' => 'المحتوى',
    ],

    'attributes' => [
        'role.en' => 'Role Name With English',
        'role.ar' => 'Role Name With Arbic',
        'permessions' => 'permessions',
        'name' => 'name',
        'password' => 'password',
        'role_id' => 'role',
        'title.ar' => 'Arabic Title',
        'title.en' => 'English Title',
        'slug.ar' => 'Arabic Slug',
        'slug.en' => 'English Slug',
        'title_ar' => 'Title in Arabic',
        'title_en' => 'Title in English',
        'skills.*.name_ar' => 'Skill name in Arabic',
        'skills.*.name_en' => 'Skill name in English',
        'skills.*.percentage' => 'Percentage',
        'counters.*.name_ar' => 'Counter name in Arabic',
        'counters.*.name_en' => 'Counter name in English',
        'counters.*.value' => 'Counter value',
        'name_ar' => 'Name in Arabic',
        'name_en' => 'Name in English',
        'job_ar' => 'Job Title in Arabic',
        'job_en' => 'Job Title in English',
        'feedback_ar' => 'Feedback in Arabic',
        'feedback_en' => 'Feedback in English',
        'question_ar' => 'Question in Arabic',
        'question_en' => 'Question in English',
        'answer_ar' => 'Answer in Arabic',
        'answer_en' => 'Answer in English',
        'position_ar' => 'Job title in Arabic',
        'position_en' => 'Job title in English',
        'experience_ar' => 'Qualifications in Arabic',
        'experience_en' => 'Qualifications in English',
        'years' => 'Years of experience',
        'bio_ar' => 'Employee specifications in Arabic',
        'bio_en' => 'Employee specifications in English',
        'number' => 'Number of Cases',
        'about_us_ar' => 'About Us in Arabic',
        'about_us_en' => 'About Us in English',
        'content.*.title_ar' => 'Additional Content Title in Arabic',
        'content.*.title_en' => 'Additional Content Title in English',
        'content.*.description_ar' => 'Additional Content Details in Arabic',
        'content.*.description_en' => 'Additional Content Details in English',
        'create_article' => 'Create a New Article',
        'status_blog' => 'Article Status',
        'content_ar' => 'Article Content in Arabic',
        'content_en' => 'Article Content in English',
        'city' => 'City Feild',
        'phone' => 'Phone Number',
        'email' => 'Email',
        'permissions' => 'Permissions',
        'name.en' => 'Name in English',
        'name.ar' => 'Name in Arabic',
    ],
];
