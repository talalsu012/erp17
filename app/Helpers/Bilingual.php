<?php

namespace App\Helpers;

class Bilingual
{
    /**
     * رسالة بالعربية والإنجليزية معاً من ملف الترجمة (عربي / English).
     *
     * @param  array<string, string|int>  $replace
     */
    public static function msg(string $key, array $replace = []): string
    {
        $arabic = trans($key, $replace, 'ar');
        $english = trans($key, $replace, 'en');

        return $arabic === $english ? $arabic : $arabic.' / '.$english;
    }
}
