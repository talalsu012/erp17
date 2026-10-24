<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

/**
 * يترجم الاسم العربي إلى الإنجليزية عبر Google Translate،
 * ويرجع إلى {@see ArabicNameTransliterator} (قاموس الأسماء السودانية) إذا تعذّرت الترجمة.
 */
class EnglishNameTranslator
{
    public function translate(string $arabicName): string
    {
        $arabicName = trim($arabicName);

        if ($arabicName === '') {
            return '';
        }

        try {
            $translated = (new GoogleTranslate)
                ->setOptions(['verify' => false])
                ->setSource('ar')
                ->setTarget('en')
                ->translate($arabicName);

            if (filled($translated)) {
                return $translated;
            }
        } catch (Throwable $e) {
            Log::warning('فشلت الترجمة التلقائية للاسم: '.$e->getMessage());
        }

        return ArabicNameTransliterator::transliterate($arabicName);
    }
}
