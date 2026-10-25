<?php

namespace App\Services\Res;

use App\Models\Res\Mark;
use App\Models\Student;

/**
 * التخرج وتصنيف الدرجة العلمية (دبلوم: المادة 25، بكالريوس: المادتان 5 و6).
 */
class GraduationService
{
    /** @deprecated القيمة الفعلية: regulations.graduation.min_gpa */
    public const MIN_GPA = 2.0;

    /**
     * المعدل التراكمي لآخر درجة لكل مقرر: Σ(نقاط×ساعات) ÷ Σ ساعات المقررات التي جلس لها.
     * المقررات المستبعدة والغياب بعذر والمقررات بلا نقاط لا تدخل في الحساب.
     */
    public function cumulativeGpa(Student $student): float
    {
        $profile = RegulationProfile::forFaculty($student->faculty_code);

        $marks = Mark::query()
            ->with('subject:code,crch')
            ->where('university_no', $student->university_no)
            ->whereNotNull('point')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Mark $m): bool => ! in_array((int) $m->reson_code, [$profile->excludedReason(), ...$profile->absentReasons()], true))
            ->unique('subject_code');

        return self::gpa(
            $marks->sum(fn (Mark $m): float => (float) $m->point * (float) ($m->subject->crch ?? 0)),
            $marks->sum(fn (Mark $m): float => (float) ($m->subject->crch ?? 0)),
        );
    }

    public static function gpa(float $points, float $hours): float
    {
        return $hours > 0 ? round($points / $hours, 2) : 0.0;
    }

    public static function meetsGraduation(float $gpa): bool
    {
        return $gpa >= RegulationProfile::current()->minGpa();
    }

    /**
     * تصنيف الدرجة العلمية، أو null إن لم يبلغ حد التخرج.
     * البكالريوس بالشرف يستخدم مراتب الشرف، وغيره التصنيف العام (ممتاز/جيد جداً/جيد/مقبول).
     */
    public static function classify(float $gpa, bool $honours = false): ?string
    {
        if (! self::meetsGraduation($gpa)) {
            return null;
        }

        $profile = RegulationProfile::current();
        $key = $honours ? 'honours' : 'general';

        foreach ($profile->graduationBands() as $band) {
            if ($gpa >= $band['min']) {
                return $band[$key];
            }
        }

        return $profile->graduationFallback()[$key];
    }
}
