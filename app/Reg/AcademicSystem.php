<?php

namespace App\Reg;

/**
 * نمط النظام الأكاديمي للجامعة (config/regulations.php → academic_system).
 *
 * - Semester: نتيجة كل فصل مستقلة، والملاحق والبدائل بعد كل فصل (السلوك الحالي).
 * - Annual: سنة كاملة بفترة واحدة (semester = 3).
 * - SemesterAnnual: نتيجة تصدر لكل فصل، لكن الملاحق والبدائل في نهاية السنة، والنتيجة النهائية من الفصلين.
 */
enum AcademicSystem: string
{
    case Semester = 'semester';
    case Annual = 'annual';
    case SemesterAnnual = 'semester_annual';

    public static function fromConfig(mixed $value): self
    {
        return self::tryFrom((string) $value) ?? self::Semester;
    }

    /** هل تُنسب الملاحق والبدائل إلى نهاية السنة بدل نهاية كل فصل؟ */
    public function supplementsAtYearEnd(): bool
    {
        return $this === self::SemesterAnnual || $this === self::Annual;
    }
}
