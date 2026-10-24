<?php

namespace App\Traits;

use ArPHP\I18N\Arabic;

trait ArabicGlyph
{
    protected static ?Arabic $arabic = null;

    protected static function arabic(): Arabic
    {
        static::$arabic ??= new Arabic('Glyphs');

        return static::$arabic;
    }

    public static function ar(?string $text): string
    {
        if (blank($text)) {
            return '';
        }

        return static::arabic()->utf8Glyphs($text);
    }
}
