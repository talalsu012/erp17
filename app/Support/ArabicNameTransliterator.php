<?php

namespace App\Support;

/**
 * تحويل الأسماء العربية إلى حروف لاتينية (للاسم الإنجليزي عند غيابه في ملف الاستيراد).
 * يعتمد قاموس أسماء شائعة بالتهجئة المعتادة في السودان، وما سواه حرفاً بحرف. يُراجع الاسم يدوياً عند الحاجة.
 */
class ArabicNameTransliterator
{
    /** @var array<string, string> أسماء شائعة بعد توحيد الهمزات والتاء المربوطة */
    private const KNOWN = [
        'محمد' => 'Mohamed', 'احمد' => 'Ahmed', 'علي' => 'Ali', 'حسن' => 'Hassan', 'حسين' => 'Hussein',
        'عبد' => 'Abd', 'عبدالله' => 'Abdalla', 'عبدالرحمن' => 'Abdelrahman', 'عبدالعزيز' => 'Abdelaziz',
        'عبدالرحيم' => 'Abdelrahim', 'عبدالكريم' => 'Abdelkarim', 'عبدالباقي' => 'Abdelbagi', 'عبدالماجد' => 'Abdelmajid',
        'ابراهيم' => 'Ibrahim', 'اسماعيل' => 'Ismail', 'يوسف' => 'Yousif', 'يعقوب' => 'Yagoub', 'عثمان' => 'Osman',
        'عمر' => 'Omer', 'خالد' => 'Khalid', 'ادم' => 'Adam', 'عيسى' => 'Eisa', 'موسى' => 'Musa', 'مصطفى' => 'Mustafa',
        'الطيب' => 'Eltayeb', 'الصادق' => 'Elsadig', 'الامين' => 'Elamin', 'الفاتح' => 'Elfatih', 'الطاهر' => 'Eltahir',
        'فاطمة' => 'Fatima', 'عائشة' => 'Aisha', 'مريم' => 'Mariam', 'خديجة' => 'Khadiga', 'زينب' => 'Zainab',
        'سارة' => 'Sara', 'اسماء' => 'Asmaa', 'امنة' => 'Amna', 'نور' => 'Nour', 'هدى' => 'Huda', 'سلمى' => 'Salma',
        'ابو' => 'Abu', 'بن' => 'Bin', 'ابن' => 'Ibn', 'الله' => 'Allah', 'الدين' => 'Eldin', 'الرحمن' => 'Elrahman',
        'سليمان' => 'Suliman', 'ابوبكر' => 'Abubakr', 'محمود' => 'Mahmoud', 'ياسر' => 'Yasir', 'طارق' => 'Tariq',
        'ربيع' => 'Rabie', 'كمال' => 'Kamal', 'جمال' => 'Jamal', 'عادل' => 'Adil', 'ماجد' => 'Majid', 'صالح' => 'Salih',
        'ادريس' => 'Idris', 'الحسين' => 'Elhussein', 'الحسن' => 'Elhassan',
    ];

    /** @var array<string, string> */
    private const LETTERS = [
        'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'a', 'ء' => '', 'ؤ' => 'o', 'ئ' => 'e',
        'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'th',
        'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z',
        'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
        'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a', 'ة' => 'a',
    ];

    public static function transliterate(?string $arabic): string
    {
        $arabic = trim((string) $arabic);

        if ($arabic === '') {
            return '';
        }

        $words = preg_split('/\s+/u', $arabic) ?: [];

        return collect($words)
            ->map(fn (string $word): string => self::word($word))
            ->filter()
            ->implode(' ');
    }

    private static function word(string $word): string
    {
        $key = self::normalize($word);

        if (isset(self::KNOWN[$key])) {
            return self::KNOWN[$key];
        }

        $latin = '';

        foreach (mb_str_split($word) as $char) {
            $latin .= self::LETTERS[$char] ?? (preg_match('/[A-Za-z0-9]/', $char) ? $char : '');
        }

        return ucfirst($latin);
    }

    /** يوحّد الهمزات والتاء المربوطة والياء ويحذف التشكيل لمطابقة القاموس */
    private static function normalize(string $word): string
    {
        $word = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', $word) ?? $word;

        return strtr($word, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ى', 'ؤ' => 'و', 'ئ' => 'ي']);
    }
}
