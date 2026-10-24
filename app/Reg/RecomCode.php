<?php

namespace App\Reg;

/**
 * أكواد التوصيات التي تُنتجها اللائحة (res_ltrecoms.code).
 */
enum RecomCode: string
{
    /** نجاح كامل */
    case Pass = '1005';
    /** إعادة بكل المقررات */
    case RepeatAll = '2001';
    /** إعادة ببعض المقررات */
    case RepeatSome = '2002';
    /** امتحان بديل (غياب بعذر) */
    case Substitute = '3001';
    /** بديل + ملحق */
    case SubstituteAndSupplementary = '3004';
    /** ملحق */
    case Supplementary = '4001';
    /** فصل أكاديمي */
    case AcademicDismissal = '4004';
    /** إزالة رسوب */
    case RemoveFail = '4005';
    /** بيانات غير مكتملة */
    case Incomplete = '9999';
}
