<?php

namespace App\Services\Res;

use App\Models\Res\Grade;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Reg\RecomCode;
use Closure;
use Illuminate\Support\Collection;

/**
 * تنفيذ اللائحة: إحصائيات الطالب (المعدل/الساعات/الرسوب) ثم التوصية النهائية.
 */
class RegulationService
{
    /** @deprecated القيم الفعلية في config/regulations.php (RegulationProfile). */
    public const PASS_MARK = 50;

    /** @deprecated القيمة الفعلية: regulations.reasons.excluded */
    public const REASON_EXCLUDED = 21;

    /** @deprecated القيمة الفعلية: regulations.reasons.absent_codes */
    public const REASON_ABSENT = 3001;

    /** @deprecated القيمة الفعلية: regulations.reasons.absent_codes */
    public const ABSENT_REASONS = [self::REASON_ABSENT, 18];

    public static function isAbsentReason(int|string|null $code): bool
    {
        return RegulationProfile::current()->isAbsentReason($code);
    }

    /**
     * يعيد حساب إحصائيات الطالب وتوصيته ويحفظهما في res_recoms.
     */
    public function refresh(
        Student $student,
        int|string $faculty,
        int|string $level,
        int|string $batch,
        int|string $semester,
        int|string $examType,
    ): Recom {
        $examType = (int) $examType;
        $semester = (int) $semester;
        $profile = RegulationProfile::forFaculty($faculty);
        $yearEnd = $examType === 2 && $profile->combinesYearForSupplements();
        $levels = $yearEnd ? app(AcademicYearService::class)->levelsOfYear($level) : [(int) $level];

        $all = Mark::query()
            ->with(['subject:code,crch', 'reason:code,recom_code'])
            ->where('university_no', $student->university_no)
            ->where('faculty_code', $faculty)
            ->whereIn('level_code', $levels)
            ->where('batch_code', $batch)
            ->when($examType === 1, fn ($q) => $q->where('examType', 1))
            ->get()
            ->filter(fn (Mark $m) => $m->mark !== null || $m->reson_code !== null)
            ->sortByDesc('id')
            ->unique('subject_code');

        $stats = $this->statistics(
            $yearEnd ? $all : $all->filter(fn (Mark $m) => (int) $m->semester === $semester),
            array_sum(array_map(fn (int $yearLevel): int => $this->expectedSubjects($student, $faculty, $yearLevel), $levels)),
            self::passMarkFor($student, $profile),
            $profile,
        );

        $included = $all->filter(fn (Mark $m) => (int) $m->reson_code !== $profile->excludedReason())->count();

        $code = self::decide(
            $examType,
            $stats,
            complete: $stats['countSubject'] === $included,
            hasPreviousRepeat: fn () => $this->hasPrevious($student, $batch, $profile->repeatHistoryCodes(), $level),
            hasPreviousExternal: fn () => $this->hasPrevious($student, $batch, $profile->externalHistoryCodes()),
            isFinalYear: $this->isFinalYear($student, $faculty, $level, $profile),
            profile: $profile,
        );

        $reasonDecision = $this->reasonDecision($all);

        if ($reasonDecision === null && $code === RecomCode::Supplementary) {
            $stats['gpa'] = 0;
        }

        $targetSemester = $profile->recomSemester($semester, $examType);

        $key = [
            'university_no' => $student->university_no,
            'faculty_code' => $faculty,
            'department_code' => $student->department_code,
            'level_code' => $level,
            'batch_code' => $batch,
            'semester' => $targetSemester,
            'examType' => $examType,
        ];

        $existing = Recom::withTrashed()->where($key)->first();

        if ($existing?->is_locked && ! auth()->user()?->isSuperAdmin()) {
            return $existing;
        }

        return Recom::withTrashed()->updateOrCreate($key, $stats + [
            'special_code' => $student->special_code,
            'recom_code' => $reasonDecision ?? $code->value,
            'deleted_at' => null,
        ]);
    }

    /**
     * قرار السبب إن كان لأحد أسباب الطالب قرار؛ فهو يغلب قرار اللائحة.
     * إن تعدد غلب الأحدث حفظاً (آخر تعديل للصف ثم الأكبر id).
     *
     * @param  Collection<int, Mark>  $marks  آخر سجل لكل مادة
     */
    private function reasonDecision(Collection $marks): ?string
    {
        $recom = $marks
            ->filter(fn (Mark $m) => $m->reason?->recom_code !== null)
            ->sortBy([['updated_at', 'desc'], ['id', 'desc']])
            ->first()?->reason->recom_code;

        return $recom === null ? null : (string) $recom;
    }

    /**
     * حد النجاح لخطة الطالب: أدنى تقدير ناجح في res_grades (مثلاً 40 للدبلوم)، وإلا PASS_MARK.
     */
    public static function passMarkFor(Student $student, ?RegulationProfile $profile = null): int|float
    {
        $profile ??= RegulationProfile::current();
        $lowest = Grade::lowestPassing((string) ($student->stady_code ?: $profile->defaultPlan()));

        return $lowest?->lower ?? $profile->passMark();
    }

    /**
     * @param  Collection<int, Mark>  $marks  آخر سجل لكل مادة
     */
    public function statistics(Collection $marks, int $subjectCount, int|float|null $passMark = null, ?RegulationProfile $profile = null): array
    {
        $profile ??= RegulationProfile::current();
        $passMark ??= $profile->passMark();
        $stats = [
            'countSubject' => $subjectCount,
            'sumMarks' => 0,
            's_pass' => 0,
            's_fail' => 0,
            'crch_fail' => 0,
            's_abs' => 0,
            'crch' => 0,
            'points' => 0,
            'gpa' => 0,
        ];

        foreach ($marks as $mark) {
            if ((int) $mark->reson_code === $profile->excludedReason()) {
                $stats['countSubject']--;

                continue;
            }

            $crch = (float) ($mark->subject->crch ?? 0);

            $stats['sumMarks'] += (float) $mark->mark;
            $stats['crch'] += $crch;
            $stats['points'] += (float) $mark->point * $crch;

            if ($profile->isAbsentReason($mark->reson_code)) {
                $stats['s_abs']++;
            } elseif ($mark->mark >= $passMark) {
                $stats['s_pass']++;
            } else {
                $stats['s_fail']++;
                $stats['crch_fail'] += $crch;
            }
        }

        $stats['gpa'] = $stats['crch'] > 0 ? round($stats['points'] / $stats['crch'], 2) : 0;

        return $stats;
    }

    /**
     * قواعد اللائحة (دالة نقية قابلة للاختبار بدون قاعدة بيانات).
     *
     * @param  Closure(): bool  $hasPreviousRepeat  هل أُعيد المستوى في دفعة سابقة
     * @param  Closure(): bool  $hasPreviousExternal  هل سبق للطالب إزالة رسوب
     * @param  bool  $isFinalYear  فصل من السنة النهائية: لا فصل بالرسوب فيه (م22)
     */
    public static function decide(
        int $examType,
        array $stats,
        bool $complete,
        Closure $hasPreviousRepeat,
        Closure $hasPreviousExternal,
        bool $isFinalYear = false,
        ?RegulationProfile $profile = null,
    ): RecomCode {
        $profile ??= RegulationProfile::current();

        if (! $complete) {
            return RecomCode::Incomplete;
        }

        $failPct = $stats['crch'] > 0 ? ($stats['crch_fail'] / $stats['crch']) * 100 : 0;

        $once = static function (Closure $fn): Closure {
            $done = false;
            $value = null;

            return static function () use (&$done, &$value, $fn) {
                if (! $done) {
                    $value = $fn();
                    $done = true;
                }

                return $value;
            };
        };
        $repeated = $once($hasPreviousRepeat);
        $external = $once($hasPreviousExternal);

        return match ($examType) {
            1 => self::decideBasicExam($stats, $failPct, $repeated, $isFinalYear, $profile),
            2 => self::decideSupplementaryExam($stats, $failPct, $repeated, $external, $profile),
            default => RecomCode::Incomplete,
        };
    }

    /**
     * نتيجة الامتحان الأساسي (examType = 1) حسب م20–22 من اللائحة:
     * رسوب أكثر من ثلث الساعات = إعادة الفصل، وأكثر من ثلثيها = فصل، وإعادة الفصل مرتين = فصل.
     * السنة النهائية لا فصل فيها بالرسوب، وتُعاد عند تجاوز الثلثين فقط.
     */
    private static function decideBasicExam(array $stats, float $failPct, Closure $repeated, bool $isFinalYear, RegulationProfile $profile): RecomCode
    {
        $hasFail = $stats['s_fail'] > 0;
        $hasAbsent = $stats['s_abs'] > 0;

        if (! $hasFail && ! $hasAbsent) {
            return RecomCode::Pass;
        }

        if (self::failExceeds($stats, 'dismiss_above', $profile)) {
            return $isFinalYear ? RecomCode::RepeatSome : RecomCode::AcademicDismissal;
        }

        if (self::failExceeds($stats, 'repeat_above', $profile) && ! $isFinalYear) {
            return $repeated() ? RecomCode::AcademicDismissal : RecomCode::RepeatSome;
        }

        return match (true) {
            ! $hasFail => RecomCode::Substitute,
            $hasAbsent => RecomCode::SubstituteAndSupplementary,
            default => RecomCode::Supplementary,
        };
    }

    /**
     * هل تتجاوز الساعات الراسبة الكسر المضبوط؟ بالضرب التبادلي لتفادي أخطاء الفاصلة (4 من 12 ليست أكثر من الثلث).
     *
     * @param  'repeat_above'|'dismiss_above'  $rule
     */
    private static function failExceeds(array $stats, string $rule, RegulationProfile $profile): bool
    {
        [$numerator, $denominator] = $profile->failFraction($rule);

        return $stats['crch'] > 0
            && ($stats['crch_fail'] * $denominator) > ($stats['crch'] * $numerator);
    }

    /**
     * هل المستوى من آخر فصول خطة القسم (عددها regulations.decisions.final_year_levels)؟
     * تُستنتج من أعلى مستويات مواد الكلية والقسم والتخصص.
     */
    private function isFinalYear(Student $student, int|string $faculty, int|string $level, RegulationProfile $profile): bool
    {
        $last = Subject::query()
            ->where('faculty_code', $faculty)
            ->where('department_code', $student->department_code)
            ->when($student->special_code, fn ($q, $special) => $q->where('special_code', $special))
            ->whereNotNull('level_code')
            ->distinct()
            ->orderByDesc('level_code')
            ->limit($profile->finalYearLevels())
            ->pluck('level_code');

        return $last->contains((int) $level);
    }

    /** نتيجة الملاحق (examType = 2) */
    private static function decideSupplementaryExam(array $stats, float $failPct, Closure $repeated, Closure $external, RegulationProfile $profile): RecomCode
    {
        $repeatAll = $profile->repeatAllAbovePct();
        $repeatSome = $profile->repeatSomeAbovePct();

        return match (true) {
            $failPct > $repeatAll && ! $repeated() => RecomCode::RepeatAll,
            $failPct > $repeatSome && $failPct <= $repeatAll && ! $repeated() => RecomCode::RepeatSome,
            $stats['s_fail'] >= 1 && $stats['s_fail'] <= $profile->removeFailMaxSubjects() && ! $external() && ! $repeated() => RecomCode::RemoveFail,
            $stats['s_fail'] > 0 && $repeated() => RecomCode::AcademicDismissal,
            default => RecomCode::Pass,
        };
    }

    /** عدد مواد الطالب المقررة (الكلية + المستوى + القسم + التخصص) */
    private function expectedSubjects(Student $student, int|string $faculty, int|string $level): int
    {
        return Subject::query()
            ->where('faculty_code', $faculty)
            ->where('level_code', $level)
            ->where('department_code', $student->department_code)
            ->when($student->special_code, fn ($q, $special) => $q->where('special_code', $special))
            ->count();
    }

    private function hasPrevious(Student $student, int|string $batch, array $codes, int|string|null $level = null): bool
    {
        return Recom::where('university_no', $student->university_no)
            ->when($level, fn ($q) => $q->where('level_code', $level))
            ->whereIn('recom_code', $codes)
            ->where('batch_code', '<>', $batch)
            ->exists();
    }
}
