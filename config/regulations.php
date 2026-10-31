<?php

/*
|--------------------------------------------------------------------------
| ملف اللائحة الأكاديمية الموحّد
|--------------------------------------------------------------------------
|
| كل قواعد اللائحة القابلة للتغيير بين جامعة وأخرى في مكان واحد. القيم الافتراضية أدناه
| هي لائحة جامعة البحر الأحمر (الدبلوم التقني والبكالريوس)، وتغييرها لا يلزمه تعديل أي كود.
| تُقرأ هذه القيم عبر App\Services\Res\RegulationProfile فقط. راجع الدليل:
| Docs/ACADEMIC_REGULATIONS_CUSTOMIZATION.md
|
| سلّم التقديرات نفسه (الحدود والنقاط) في جدول res_grades وليس هنا، وأسباب الدرجات في res_resons.
|
*/

$regulation = [

    /*
    | النظام الأكاديمي: semester | annual | semester_annual
    */
    'academic_system' => env('REG_ACADEMIC_SYSTEM', 'semester'),

    /*
    | خطة التقديرات الافتراضية (reg_ltcolumns.code) عند خلو بيانات الطالب من الخطة،
    | وحد النجاح الاحتياطي عند غياب نطاق ناجح في res_grades للخطة.
    */
    'default_plan' => env('REG_DEFAULT_PLAN', '20060001'),
    'pass_mark' => 50,

    /*
    | mode: percent = القرار بنسبة الساعات الراسبة (repeat_*_above_pct)، وcount = بعدد المقررات الراسبة:
    | حتى repeat_some_max_subjects مقرراً إعادة هذه المقررات، وأكثر منها إعادة الفصل كله.
    |
    | امتحان الملاحق: أقصى درجة، ونقاط الناتج إن لم يُعرَّف تقدير ناجح للخطة.
    | النسب نسب الساعات الراسبة من ساعات الفصل: فوق الأولى إعادة السنة/الفصل كله،
    | وبين الثانية والأولى إعادة بعض المقررات. وأقصى عدد مقررات راسبة يُسمح بإزالة رسوبه.
    */
    'supplementary' => [
        'max_mark' => 50,
        'pass_point' => 2.00,
        'repeat_all_above_pct' => 70,
        'repeat_some_above_pct' => 50,
        'remove_fail_max_subjects' => 1,
        'mode' => 'percent',
        'repeat_some_max_subjects' => 2,
    ],

    /*
    | رموز الأسباب (res_resons.code) التي يعتمد عليها الحساب.
    | excluded: مستبعد من الحساب. absent_codes: غياب بعذر (الحالي والقديم) فلا يُحسب نجاحاً ولا رسوباً.
    */
    'reasons' => [
        'excluded' => 21,
        'absent_codes' => [3001, 18],
    ],

    /*
    | قرارات الامتحان الأساسي (الدور الأول):
    | repeat_above: رسوب أكثر من هذا الكسر من الساعات = إعادة. dismiss_above: أكثر من هذا الكسر = فصل
    | (السنة النهائية تُعاد ولا تُفصل). final_year_levels: عدد آخر مستويات الخطة المعدودة سنة نهائية.
    | inclusive: true = تساوي الحد يكفي (رسوب 50% بالضبط = إعادة)، وfalse = يجب تجاوزه.
    | repeat_history_codes / external_history_codes: رموز توصيات سابقة تُعد إعادة سابقة / إزالة رسوب سابقة.
    */
    'decisions' => [
        'repeat_above' => [1, 3],
        'dismiss_above' => [2, 3],
        'final_year_levels' => 2,
        'inclusive' => false,
        'repeat_history_codes' => [2001, 2002],
        'external_history_codes' => [4005],
    ],

    /*
    | التخرج: أدنى معدل تراكمي، ومراتب التصنيف من الأعلى للأدنى (الحد الأدنى للمعدل + تسمية عامة وتسمية الشرف).
    | آخر مرتبة (fallback) تُمنح لمن بلغ حد التخرج ولم يبلغ أي مرتبة أعلى.
    */
    'graduation' => [
        'min_gpa' => 2.0,
        'bands' => [
            ['min' => 3.5, 'general' => 'ممتاز', 'honours' => 'مرتبة الشرف الأولى'],
            ['min' => 3.0, 'general' => 'جيد جداً', 'honours' => 'مرتبة الشرف الثانية (القسم الأول)'],
            ['min' => 2.5, 'general' => 'جيد', 'honours' => 'مرتبة الشرف الثانية (القسم الثاني)'],
        ],
        'fallback' => ['general' => 'مقبول', 'honours' => 'مرتبة الشرف الثالثة'],
    ],

    /*
    | الحرمان بالغياب: نسبة الإنذار ونسبة الحرمان (بعد تجاوزها)، وهل يُحتسب الغياب بعذر غياباً.
    */
    'absence' => [
        'warning_pct' => 15,
        'deprivation_pct' => 25,
        'count_excused' => true,
    ],

    /*
    | تجاوزات جزئية لكل كلية: '<faculty_code>' => ['decisions' => ['final_year_levels' => 1], ...]
    | القيم المحددة فقط تُستبدل، والمصفوفات القائمية (مثل absent_codes) تُستبدل كاملة.
    */
    'faculties' => [],

    /*
    | حذف مشغّلات ودالة وإجراء حساب الرسوم القديمة من القاعدة عند تنفيذ الـ migrations.
    | يبقى false حتى لا تتغير القاعدة الحالية؛ فعّله فقط عند تحويل النظام إلى StudentFeeService.
    */
    'drop_legacy_fee_triggers' => (bool) env('DROP_LEGACY_FEE_TRIGGERS', false),

];

/*
| لائحة جامعة كسلا (2023): تُفعَّل بـ REG_PROFILE=kassala في .env.
| الدور الأول: الإعادة عند رسوب 50% فأكثر من الساعات والفصل عند 75% فأكثر (م 3-13 إلى 5-13).
| الدور الثاني: بعدد المقررات الراسبة، مقرر أو مقرران إعادة بها وثلاثة فأكثر إعادة الفصل.
*/
$profiles = [
    'kassala' => [
        'supplementary' => ['mode' => 'count', 'repeat_some_max_subjects' => 2],
        'decisions' => ['repeat_above' => [1, 2], 'dismiss_above' => [3, 4], 'inclusive' => true],
    ],
];

return array_replace_recursive($regulation, $profiles[(string) env('REG_PROFILE')] ?? []);
