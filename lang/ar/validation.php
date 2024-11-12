<?php

return [
    'accepted' => 'يجب قبول :attribute.',
    'accepted_if' => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url' => ':attribute ليس عنوان URL صحيح.',
    'after' => 'يجب أن يكون :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون :attribute تاريخًا بعد أو يساوي :date.',
    'alpha' => ':attribute يجب أن يحتوي فقط على حروف.',
    'alpha_dash' => ':attribute يمكن أن يحتوي فقط على حروف، أرقام، شرطات وشرطات سفلية.',
    'alpha_num' => ':attribute يمكن أن يحتوي فقط على حروف وأرقام.',
    'array' => ':attribute يجب أن يكون مصفوفة.',
    'before' => 'يجب أن يكون :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون :attribute تاريخًا قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على بين :min و :max حرف.',
        'array' => 'يجب أن يحتوي :attribute على بين :min و :max عناصر.',
    ],
    'boolean' => 'يجب أن يكون الحقل :attribute صحيحًا أو خطأ.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'date' => ':attribute ليس تاريخًا صحيحًا.',
    'date_equals' => 'يجب أن يكون :attribute تاريخًا يساوي :date.',
    'date_format' => ':attribute لا يتطابق مع تنسيق التاريخ :format.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يكون :attribute عدد أرقام :digits.',
    'digits_between' => 'يجب أن يكون :attribute عدد أرقام بين :min و :max.',
    'dimensions' => 'يحتوي :attribute على أبعاد صورة غير صالحة.',
    'distinct' => 'يحتوي حقل :attribute على قيمة مكررة.',
    'email' => ':attribute يجب أن يكون عنوان بريد إلكتروني صالحًا.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'exists' => ':attribute المحدد غير موجود.',
    'file' => ':attribute يجب أن يكون ملفًا.',
    'filled' => 'يجب أن يحتوي حقل :attribute على قيمة.',
    'gt' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'file' => 'يجب أن يكون حجم :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على أكثر من :value حرف.',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'file' => 'يجب أن يكون حجم :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على :value حرف أو أكثر.',
        'array' => 'يجب أن يحتوي :attribute على :value عناصر أو أكثر.',
    ],
    'image' => ':attribute يجب أن يكون صورة.',
    'in' => ':attribute المحدد غير صحيح.',
    'in_array' => 'الحقل :attribute لا يوجد في :other.',
    'integer' => ':attribute يجب أن يكون عددًا صحيحًا.',
    'ip' => ':attribute يجب أن يكون عنوان IP صالحًا.',
    'ipv4' => ':attribute يجب أن يكون عنوان IPv4 صالحًا.',
    'ipv6' => ':attribute يجب أن يكون عنوان IPv6 صالحًا.',
    'json' => ':attribute يجب أن يكون سلسلة JSON صالحة.',
    'lt' => [
        'numeric' => 'يجب أن يكون :attribute أقل من :value.',
        'file' => 'يجب أن يكون حجم :attribute أقل من :value كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على أقل من :value حرف.',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عناصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :value.',
        'file' => 'يجب أن يكون حجم :attribute أقل من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على :value حرف أو أقل.',
        'array' => 'يجب أن يحتوي :attribute على :value عناصر أو أقل.',
    ],
    'max' => [
        'numeric' => 'لا يمكن أن يكون :attribute أكبر من :max.',
        'file' => 'لا يمكن أن يكون حجم :attribute أكبر من :max كيلوبايت.',
        'string' => 'لا يمكن أن يحتوي :attribute على أكثر من :max حرف.',
        'array' => 'لا يمكن أن يحتوي :attribute على أكثر من :max عناصر.',
    ],
    'mimes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'min' => [
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'file' => 'يجب أن يكون حجم :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على الأقل :min حرف.',
        'array' => 'يجب أن يحتوي :attribute على الأقل :min عناصر.',
    ],
    'not_in' => ':attribute المحدد غير صحيح.',
    'not_regex' => 'تنسيق :attribute غير صحيح.',
    'numeric' => ':attribute يجب أن يكون رقمًا.',
    'present' => 'يجب أن يكون حقل :attribute موجودًا.',
    'regex' => 'تنسيق :attribute غير صحيح.',
    'required' => 'يجب ملء حقل :attribute.',
    'required_if' => 'يجب ملء حقل :attribute عندما يكون :other هو :value.',
    'required_unless' => 'يجب ملء حقل :attribute ما لم يكن :other في :values.',
    'required_with' => 'يجب ملء حقل :attribute عندما يكون :values موجودًا.',
    'required_with_all' => 'يجب ملء حقل :attribute عندما تكون :values موجودة.',
    'required_without' => 'يجب ملء حقل :attribute عندما لا يكون :values موجودًا.',
    'required_without_all' => 'يجب ملء حقل :attribute عندما لا تكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'numeric' => 'يجب أن يكون :attribute بحجم :size.',
        'file' => 'يجب أن يكون حجم :attribute :size كيلوبايت.',
        'string' => 'يجب أن يحتوي :attribute على :size حرف.',
        'array' => 'يجب أن يحتوي :attribute على :size عناصر.',
    ],
    'string' => ':attribute يجب أن يكون سلسلة نصية.',
    'timezone' => ':attribute يجب أن يكون منطقة زمنية صالحة.',
    'unique' => ':attribute مُستخدم بالفعل.',
    'uploaded' => 'فشل في تحميل :attribute.',
    'url' => 'تنسيق :attribute غير صحيح.',
    'uuid' => ':attribute يجب أن يكون UUID صالحًا.',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
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

    'attributes' => [],

];
