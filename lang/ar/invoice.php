<?php

/*
|--------------------------------------------------------------------------
| Registration Reset Language Lines
|--------------------------------------------------------------------------
|
| The following language lines are the default lines which match reasons
| that are given by the password broker for a password update attempt
| outcome such as failure due to an invalid password / reset token.
|
*/
return [
    // invoice arabic

    'name_ar' => 'الاسم',
    'invoice_no' => 'رقم الفاتورة',
    'amount' => 'المبلغ',
    'sys_type' => 'نوع النظام',
    'faculty_code' => 'الكلية',
    'description' => 'الوصف',
    'email' => 'البريد الإلكتروني',
    'phone' => 'رقم الهاتف',
    'bank_pay' => 'دفع عبر البنك',
    'due_date' => 'تاريخ الاستحقاق',
    'expire_at' => 'تاريخ الانتهاء',
    'status' => 'الحالة',
    'bank' => 'اسم البنك',
    'bankdate' => 'تاريخ الدفع البنكي',
    'voucher_no' => 'رقم الإيصال',

    'bank_pay_options' => [
        'bank' => 'دفع بنكي',
        'non_bank' => 'غير بنكي',
    ],

    'bulk_fees' => [
        'title' => 'إدخال الرسوم جماعياً',
        'fee_type' => 'نوع الرسم',
        'amount' => 'المبلغ',
        'preview' => 'معاينة',
        'apply' => 'تطبيق على الطلاب',
        'preview_heading' => 'عدد الطلاب المطابقين: :count',
        'before_total' => 'الإجمالي قبل',
        'after_total' => 'الإجمالي بعد',
        'confirm_heading' => 'تأكيد إدخال الرسوم',
        'confirm_description' => 'ستُحدَّث رسوم :count طالباً ويُعاد حساب أقساطهم.',
        'done' => 'تم تحديث رسوم :count طالباً',
        'log' => 'إدخال رسوم دراسية جماعياً',
        'filters_required' => 'اختر الكلية والقسم والفصل والعام الدراسي.',
        'invalid_fee_type' => 'نوع الرسم غير صالح.',
        'invalid_amount' => 'المبلغ غير صالح.',
        'forbidden' => 'لا تملك صلاحية إدخال الرسوم جماعياً.',
    ],

];
