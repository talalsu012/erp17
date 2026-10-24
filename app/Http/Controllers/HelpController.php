<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * أدلة المساعدة داخل النظام (للمستخدمين غير المبرمجين). تُفتح من قائمة الملف الشخصي.
 */
class HelpController extends Controller
{
    /** @var array<string, string> المفتاح في الرابط => عنوان الدليل */
    public const GUIDES = [
        'students' => 'دليل إدارة الطلاب',
        'entry-marks' => 'دليل إدخال الدرجات',
        'entry-marks_redsea' => 'دليل إدخال الدرجات: جامعة البحر الأحمر',
        'result-print' => 'دليل طباعة النتيجة',
    ];

    public function __invoke(string $guide): View
    {
        abort_unless(array_key_exists($guide, self::GUIDES), 404);

        return view('help.'.$guide);
    }
}
