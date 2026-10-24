<?php

namespace App\Reg;

/**
 * Enum لتحديد أكواد التوصيات النهائية للطلاب
 */
enum ShowAbsentCode: string
{
    // إعادة بكل المقررات[2001, 2002]
    case ReAllCourses = '2001';

    // إعادة ببعض المقررات
    case ReSomeCourses = '2002';

    // بديل
    case Substitute = '3001';
    // [4002, 4004] فصل دراسي وفصل اداري
    case AdministrativeLeave = '4002';
    case AcademicLeave = '4004';

    // بديل + ملحق
    case SubstituteAndSupplementary = '3004';

    // ملحق
    case Supplementary = '4001';
    //    // غائب
    case FailMarkEn = 'F';
    case FailMarkAr = 'ر';
    case FailMarkAr2 = 'رسوب';
    case SubstituteMark = 'بديل';
    case SubstituteMarkD = 'D*';
    case SubstituteMarkF = 'F*';

    case Freezed = 'ت';
    case FreezedRecommendation = 'تجميد';

    /**
     * دالة مساعدة لجلب قيم الطلاب الملحقين أو البدلاء فقط
     *
     * @return array<int, string>
     */
    public static function getRemedialValues(): array
    {
        return [
            // self::Substitute->value,
            // self::ReSomeCourses->value,
            // self::ReAllCourses->value,
            // self::SubstituteAndSupplementary->value,
            self::Supplementary->value,
        ];
    }

    public static function getMarkValues(): array
    {
        return [
            self::FailMarkEn->value,
            self::FailMarkAr->value,
            self::FailMarkAr2->value,
            self::SubstituteMark->value,
            self::SubstituteMarkD->value,
            self::SubstituteMarkF->value,
        ];
    }

    // PreviousRepeatCount
    public static function Repeat(): array
    {
        return [
            self::ReAllCourses->value,
            self::ReSomeCourses->value,

        ];
    }

    // PreviousExternalCount
    public static function External(): array
    {
        return [
            self::AdministrativeLeave->value,
            self::AcademicLeave->value,

        ];
    }
}
