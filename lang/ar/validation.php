<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted' => 'يجب قبول :attribute',
    'active_url' => ':attribute لا يُمثّل رابطًا صحيحًا',
    'after' => 'يجب على :attribute أن يكون تاريخًا لاحقًا للتاريخ :date.',
    'after_or_equal' => ':attribute يجب أن يكون تاريخاً لاحقاً أو مطابقاً للتاريخ :date.',
    'alpha' => 'يجب أن لا يحتوي :attribute سوى على حروف',
    'alpha_dash' => 'يجب أن لا يحتوي :attribute سوى على حروف، أرقام ومطّات.',
    'alpha_num' => 'يجب أن يحتوي :attribute على حروفٍ وأرقامٍ فقط',
    'array' => 'يجب أن يكون :attribute ًمصفوفة',
    'before' => 'يجب على :attribute أن يكون تاريخًا سابقًا للتاريخ :date.',
    'before_or_equal' => ':attribute يجب أن يكون تاريخا سابقا أو مطابقا للتاريخ :date',
    'between' => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون عدد حروف النّص :attribute بين :min و :max',
        'array' => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute إما true أو false ',
    'unique_translation_rule' => ':attribute مستخدم من قبل.',
    'confirmed' => 'حقل التأكيد غير مُطابق للحقل :attribute',
    'date' => ':attribute ليس تاريخًا صحيحًا',
    'date_format' => 'لا يتوافق :attribute مع الشكل :format.',
    'different' => 'يجب أن يكون الحقلان :attribute و :other مُختلفان',
    'digits' => 'يجب أن يحتوي :attribute على :digits رقمًا/أرقام',
    'digits_between' => 'يجب أن يحتوي :attribute بين :min و :max رقمًا/أرقام ',
    'dimensions' => 'الـ :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'للحقل :attribute قيمة مُكرّرة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح البُنية',
    'exists' => 'القيمة المحددة :attribute غير موجودة',
    'file' => 'الـ :attribute يجب أن يكون ملفا.',
    'filled' => ':attribute إجباري',
    'gt' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت',
        'string' => 'يجب أن يكون طول النّص :attribute أكثر من :value حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر/عنصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر من :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :value كيلوبايت',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :value حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :value عُنصرًا/عناصر',
    ],
    'image' => 'يجب أن يكون :attribute صورةً',
    'in' => ':attribute غير موجود',
    'in_array' => ':attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا',
    'ip' => 'يجب أن يكون :attribute عنوان IP صحيحًا',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صحيحًا.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صحيحًا.',
    'json' => 'يجب أن يكون :attribute نصآ من نوع JSON.',
    'lt' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أصغر من :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute أصغر من :value كيلوبايت',
        'string' => 'يجب أن يكون طول النّص :attribute أقل من :value حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عناصر/عنصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :max.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :max حروفٍ/حرفًا',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'max' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :max.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :max حروفٍ/حرفًا',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'mimes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'mimetypes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'min' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر من :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :min حروفٍ/حرفًا',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عُنصرًا/عناصر',
    ],
    'not_in' => ':attribute موجود',
    'not_regex' => 'صيغة :attribute غير صحيحة.',
    'numeric' => 'يجب على :attribute أن يكون رقمًا',
    'present' => 'يجب تقديم :attribute',
    'regex' => 'صيغة :attribute .غير صحيحة',
    'required' => ':attribute مطلوب.',
    'required_if' => ':attribute مطلوب في حال ما إذا كان :other يساوي :value.',
    'required_unless' => ':attribute مطلوب في حال ما لم يكن :other يساوي :values.',
    'required_with' => ':attribute مطلوب إذا توفّر :values.',
    'required_with_all' => ':attribute مطلوب إذا توفّر :values.',
    'required_without' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'required_without_all' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'same' => 'يجب أن يتطابق :attribute مع :other',
    'size' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية لـ :size',
        'file' => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت',
        'string' => 'يجب أن يحتوي النص :attribute على :size حروفٍ/حرفًا بالضبط',
        'array' => 'يجب أن يحتوي :attribute على :size عنصرٍ/عناصر بالضبط',
    ],
    'string' => 'يجب أن يكون :attribute نصآ.',
    'timezone' => 'يجب أن يكون :attribute نطاقًا زمنيًا صحيحًا',
    'unique' => 'قيمة :attribute مُستخدمة من قبل',
    'uploaded' => 'فشل في تحميل الـ :attribute',
    'url' => 'صيغة الرابط :attribute غير صحيحة',

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
            'required' => 'يجب عليك التحقق من أنك لست روبوت',
        ],
        'email' => [
            'required' => 'الإيميل مطلوب',
        ],
        'password' => [
            'required' => 'الباسورد مطلوب',
        ],

        'name' => [
            'required' => 'الإسم مطلوب',
        ],

        'code' => [
            'required' => 'رمز التحقق مطلوب',
            'min' => 'يجب أن يحتوي الرمز على 5 أرقام على الأقل.',
        ],

        'mainemail' => [
            'required' => 'الإيميل الرسمي مطلوب',
        ],
        'logo' => [
            'required' => 'اللوجو مطلوب',
        ],
        'phonenumber' => [
            'required' => 'رقم الجوال مطلوب',
        ],
         'company_name' => [
        'required' => 'اسم الشركة مطلوب',
    ],
        'adress_ar' => [
            'required' => 'العنوان بالعربية',
        ],
        'about_ar' => [
            'required' => 'من نحن بالعربية',
        ],
        'adress_en' => [
            'required' => 'العنوان بالإنجليزية',
        ],
        'about_en' => [
            'required' => 'من نحن بالإنجليزية',
        ],
        'social_links.*.link' => [
            'required' => 'لينك الحساب  مطلوب',
            'url' => 'لينك الحساب  يجب أن يكون رابطاً صالحاً',
        ],
        'social_links.*.icon' => [
            'required' => 'أيقونة الحساب مطلوبة',
            'string' => 'أيقونة الحساب يجب أن تكون نصية',
        ],
        'clients.*.link' => [
            'required' => 'لينك العميل مطلوب.',
            'url' => 'لينك العميل  يجب أن يكون رابطًا صالحًا.',
        ],
        'clients.*.icon' => [
            'required' => 'الأيقونة رقم للعميل مطلوبة.',
        ],
        'title.ar' => [
            'required' => 'العنوان بالعربية مطلوب',
            'string' => 'العنوان بالعربية يجب أن يكون نصًا',
            'max' => 'العنوان بالعربية لا يجب أن يتجاوز 255 حرف',
        ],
        'title.en' => [
            'required' => 'العنوان بالإنجليزية مطلوب',
            'string' => 'العنوان بالإنجليزية يجب أن يكون نصًا',
            'max' => 'العنوان بالإنجليزية لا يجب أن يتجاوز 255 حرف',
        ],
        'slug.ar' => [
            'required' => 'اللينك بالعربية مطلوب',
            'string' => 'اللينك بالعربية يجب أن يكون نصًا',
            'max' => 'اللينك بالعربية لا يجب أن يتجاوز 255 حرف',
        ],
        'slug.en' => [
            'required' => 'اللينك بالإنجليزية مطلوب',
            'string' => 'اللينك بالإنجليزية يجب أن يكون نصًا',
            'max' => 'اللينك بالإنجليزية لا يجب أن يتجاوز 255 حرف',
        ],
        'description_ar' => [
            'required' => 'محتوى الخدمة بالعربية مطلوب',
            'string' => 'محتوى الخدمة بالعربية يجب أن يكون نصًا',
        ],
        'description_en' => [
            'required' => 'محتوى الخدمة بالإنجليزية مطلوب',
            'string' => 'محتوى الخدمة بالإنجليزية يجب أن يكون نصًا',
        ],
        'meta_description_ar' => [
            'required' => 'الميتا ديسكريبشن بالعربية مطلوب',
            'string' => 'الميتا ديسكريبشن بالعربية يجب أن يكون نصًا',
            'max' => 'الميتا ديسكريبشن بالعربية لا يجب أن يتجاوز 170 حرف',
        ],
        'meta_description_en' => [
            'required' => 'الميتا ديسكريبشن بالإنجليزية مطلوب',
            'string' => 'الميتا ديسكريبشن بالإنجليزية يجب أن يكون نصًا',
            'max' => 'الميتا ديسكريبشن بالإنجليزية لا يجب أن يتجاوز 170 حرف',
        ],
        'meta_keywords_ar' => [
            'required' => 'الكلمات المفتاحية بالعربية مطلوبة',
            'string' => 'الكلمات المفتاحية بالعربية يجب أن تكون نصًا',
            'max' => 'الكلمات المفتاحية بالعربية لا يجب أن تتجاوز 1000 حرف',
        ],
        'meta_keywords_en' => [
            'required' => 'الكلمات المفتاحية بالإنجليزية مطلوبة',
            'string' => 'الكلمات المفتاحية بالإنجليزية يجب أن تكون نصًا',
            'max' => 'الكلمات المفتاحية بالإنجليزية لا يجب أن تتجاوز 1000 حرف',
        ],
        'meta_title_ar' => [
            'required' => 'الميتا تايتل بالعربية مطلوب',
            'string' => 'الميتا تايتل بالعربية يجب أن يكون نصًا',
            'max' => 'الميتا تايتل بالعربية لا يجب أن يتجاوز 70 حرف',
        ],
        'meta_title_en' => [
            'required' => 'الميتا تايتل بالإنجليزية مطلوب',
            'string' => 'الميتا تايتل بالإنجليزية يجب أن يكون نصًا',
            'max' => 'الميتا تايتل بالإنجليزية لا يجب أن يتجاوز 70 حرف',
        ],
        'image' => [
            'required' => 'الصورة مطلوبة',
            'image' => 'الملف يجب أن يكون صورة',
            'mimes' => 'الصورة يجب أن تكون من نوع jpeg، png، jpg، gif، svg',
            'max' => 'حجم الصورة لا يجب أن يتجاوز 2 ميجابايت',
        ],
        'status' => [
            'required' => 'الحالة مطلوبة',
            'boolean' => 'الحالة يجب أن تكون قيمة منطقية (صح أو خطأ)',
        ],
        'firstsolgan_ar' => [
            'required' => 'السلوجن الأول باللغة العربية مطلوب.',
            'max' => 'الشعار الأول باللغة العربية يجب ألا يزيد عن :max حرف.',
        ],
        'firstsolgan_en' => [
            'required' => 'السلوجن الأول باللغة الإنجليزية مطلوب.',
            'max' => 'الشعار الأول باللغة الإنجليزية يجب ألا يزيد عن :max حرف.',
        ],
        'secondsolgan_ar' => [
            'required' => 'السلوجن الثاني باللغة العربية مطلوب.',
            'max' => 'الشعار الأول باللغة العربية يجب ألا يزيد عن :max حرف.',
        ],
        'secondsolgan_en' => [
            'required' => 'السلوجن الثاني باللغة الإنجليزية مطلوب.',
            'max' => 'الشعار الأول باللغة الإنجليزية يجب ألا يزيد عن :max حرف.',
        ],
        'textbutton_ar' => [
            'required' => ' نص الباتون باللغة العربية مطلوب.',
        ],
        'textbutton_en' => [
            'required' => 'نص الباتون  باللغة الإنجليزية مطلوب.',
        ],
        'link' => [
            'required' => 'الرابط مطلوب.',
            'url' => 'يجب أن يكون الرابط بصيغة صحيحة.',
            'max' => 'يجب ألا يزيد الرابط عن :max حرف.',
        ],
        'features.*.text_ar' => [
            'required' => 'النص العربي لكل ميزة مطلوب.',
            'string' => 'النص العربي يجب أن يكون نصًا صالحًا.',
            'max' => 'النص العربي لا يجب أن يزيد عن :max حرف.',
        ],
        'features.*.text_en' => [
            'required' => 'النص الإنجليزي لكل ميزة مطلوب.',
            'string' => 'النص الإنجليزي يجب أن يكون نصًا صالحًا.',
            'max' => 'النص الإنجليزي لا يجب أن يزيد عن :max حرف.',
        ],
        'features.*.image' => [
            'image' => 'يجب أن تكون الصورة ملف صورة صالح.',
            'mimes' => 'يجب أن تكون الصورة بصيغة: jpeg, png, jpg, gif, svg.',
            'max' => 'يجب ألا يزيد حجم الصورة عن :max كيلوبايت.',
        ],

        'categories.*.ar' => [
            'required' => 'اسم القسم بالعربية مطلوب.',
            'string' => 'اسم القسم بالعربية يجب أن يكون نصًا.',
            'min' => 'اسم القسم بالعربية يجب ألا يقل عن :min حرف.',
            'max' => 'اسم القسم بالعربية يجب ألا يزيد عن :max حرف.',
        ],

        'categories.*.en' => [
            'required' => 'اسم القسم بالإنجليزية مطلوب.',
            'string' => 'اسم القسم بالإنجليزية يجب أن يكون نصًا.',
            'min' => 'اسم القسم بالإنجليزية يجب ألا يقل عن :min حرف.',
            'max' => 'اسم القسم بالإنجليزية يجب ألا يزيد عن :max حرف.',
        ],

        'categories.*.order' => [
            'required' => 'رقم الترتيب مطلوب.',
            'integer' => 'رقم الترتيب يجب أن يكون رقمًا صحيحًا.',
            'min' => 'رقم الترتيب يجب ألا يكون أقل من :min.',
        ],

        'categories.*.status' => [
            'required' => 'حالة القسم مطلوبة.',
            'in' => 'حالة القسم يجب أن تكون مفعل أو معطل.',
        ],

        'categories.*.id' => [
            'integer' => 'رقم المعرّف يجب أن يكون رقمًا صحيحًا.',
            'exists' => 'القسم المطلوب تعديله غير موجود.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'الاسم',
        'username' => 'اسم المُستخدم',
        'email' => 'البريد الالكتروني',
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
        'role.en' => ' اسم الصلاحية ب الانجليزي',
        'role.ar' => 'اسم الصلاحية ب العربي   ',
        'permessions' => 'الصلاحيات',
        'name' => 'الإسم',
        'password' => 'الباسورد',
        'role_id' => 'الصلاحية',
        'title.ar' => 'العنوان بالعربية',
        'title.en' => 'العنوان بالإنجليزية',
        'slug.ar' => 'اللينك بالعربية',
        'slug.en' => 'اللينك بالإنجليزية',
        'title_ar' => 'العنوان بالعربي',
        'title_en' => 'العنوان بالإنجليزية',
        'skills.*.name_ar' => 'إسم المهارة بالعربية',
        'skills.*.name_en' => 'إسم المهارة بالإنجليزية',
        'skills.*.percentage' => 'النسبة المئوية',
        'counters.*.name_ar' => 'إسم الكاونتر بالعربية',
        'counters.*.name_en' => 'إسم الكاونتر بالإنجليزية',
        'counters.*.value' => 'قيمة العداد',
        'name_ar' => 'الإسم بالعربية',
        'name_en' => 'الإسم بالإنجليزية',
        'job_ar' => 'المسمى الوظيفي بالعبية',
        'job_en' => 'المسمى الوظيفي بالإنجليزية',
        'feedback_ar' => 'الفيد باك بالعربية',
        'feedback_en' => 'الفيد باك بالإنجليزية',
        'question_ar' => 'السؤال بالعربية',
        'question_en' => 'السؤال بالإنجليزية',
        'answer_ar' => 'الجواب بالعربي',
        'answer_en' => 'الجواب بالإنجليزية',
        'position_ar' => 'المسمى الوظيفي بالعربي',
        'position_en' => 'المسمى الوظيفي بالإنجليزية',
        'experience_ar' => 'المؤهلات بالعربي',
        'experience_en' => 'المؤهلات بالإنجليزي',
        'years' => 'سنوات الخبرة',
        'bio_ar' => 'مواصفات الموظف بالعربي',
        'bio_en' => 'مواصفات الموظف بالإنجليزي',
        'number' => 'عدد القضايا بالعربي',
        'about_us_ar' => 'من نحن بالعربي',
        'about_us_en' => 'من نحن بالإنجليزية',
        'content.*.title_ar' => 'عنوان المحتوي الإضافي بالعربي',
        'content.*.title_en' => 'عنوان المحتوي الإضافي بالإنجليزية',
        'content.*.description_ar' => 'تفاصيل المحتوي الإضافي بالعربي',
        'content.*.description_en' => 'تفاصيل المحتوي الإضافي بالإنجليزية',
        'status_blog' => 'حالة المقالة',
        'content_ar' => 'محتوى المقالة بالعربي',
        'content_en' => 'محتوى المقالة بالإنجليزي',
        'city' => 'حقل المدينة',
        'phone' => 'رقم الجوال',
        'email' => 'الإيميل',
        'permissions' => 'الصلاحيات',
        'name.en' => 'الإسم بالإنجليزية',
        'name.ar' => 'الإسم بالعربية',
    ],
];
