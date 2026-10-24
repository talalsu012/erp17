<?php

namespace App\Services\Res;

use App\Models\Res\Mark;
use App\Models\Student;

/**
 * التخرج وتصنيف الدرجة العلمية (دبلوم: المادة 25، بكالريوس: المادتان 5 و6).
 */
class GraduationService
{
    /** أدنى معدل تراكمي للتخرج */
    public const MIN_GPA = 2.0;

    /**
     * المعدل التراكمي لآخر درجة لكل مقرر: Σ(نقاط×ساعات) ÷ Σ ساعات المقررات التي جلس لها.
     * المقررات المستبعدة والغياب بعذر والمقررات بلا نقاط لا تدخل في الحساب.
     */
    public function cumulativeGpa(Student $student): float
    {
        $marks = Mark::query()
            ->with('subject:code,crch')
            ->where('university_no', $student->university_no)
            ->whereNotNull('point')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Mark $m): bool => ! in_array((int) $m->reson_code, [RegulationService::REASON_EXCLUDED, ...RegulationService::ABSENT_REASONS], true))
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
        return $gpa >= self::MIN_GPA;
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

        if ($honours) {
            return match (true) {
                $gpa >= 3.5 => 'مرتبة الشرف الأولى',
                $gpa >= 3.0 => 'مرتبة الشرف الثانية (القسم الأول)',
                $gpa >= 2.5 => 'مرتبة الشرف الثانية (القسم الثاني)',
                default => 'مرتبة الشرف الثالثة',
            };
        }

        return match (true) {
            $gpa >= 3.5 => 'ممتاز',
            $gpa >= 3.0 => 'جيد جداً',
            $gpa >= 2.5 => 'جيد',
            default => 'مقبول',
        };
    }
}
