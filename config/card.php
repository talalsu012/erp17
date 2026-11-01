<?php

/*
|--------------------------------------------------------------------------
| Student card texts
|--------------------------------------------------------------------------
|
| Every fixed text printed on the student card (front, back and the preview)
| is read from here. The university name and logo come from config/university.php.
|
*/

return [
    'badge_ar' => 'بطاقة طالب',
    'badge_en' => 'STUDENT ID',

    'issuer_ar' => 'عمادة شؤون الطلاب',
    'issuer_en' => 'Student Affairs Deanship',

    'website' => env('CARD_WEBSITE', 'www.kassalauni.edu.sd'),

    'default_color' => '#0A522E',

    'labels' => [
        'full_name' => 'الاسم الكامل',
        'university_no' => 'الرقم الجامعي',
        'faculty' => 'الكلية',
        'level' => 'الفصل',
        'batch' => 'العام الدراسي',
        'status' => 'الحالة',
        'active' => 'نشط',
        'expiry' => 'صالحة حتى',
        'card_number' => 'رقم البطاقة',
        'signature' => 'توقيع حامل البطاقة',
    ],

    'instructions' => [
        'show_card' => 'يجب إبراز هذه البطاقة',
        'show_card_at' => 'عند الدخول للحرم الجامعي',
        'scan' => 'امسح رمز QR للتحقق من صحة البطاقة وبيانات حاملها',
        'return' => 'عند العثور على البطاقة تُعاد إلى',
    ],
];
