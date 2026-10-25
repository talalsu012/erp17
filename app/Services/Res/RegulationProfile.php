<?php

namespace App\Services\Res;

use App\Reg\AcademicSystem;
use Illuminate\Support\Arr;

/**
 * المدخل الوحيد لقواعد اللائحة الأكاديمية: يقرأ config/regulations.php ويدمج تجاوزات الكلية.
 * الخدمات تستعمل RegulationProfile::current() أو forFaculty($code) ولا تقرأ الإعدادات مباشرة.
 */
final class RegulationProfile
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private readonly array $data) {}

    public static function current(): self
    {
        return self::forFaculty(null);
    }

    public static function forFaculty(int|string|null $facultyCode): self
    {
        $base = (array) config('regulations', []);
        $overrides = $facultyCode === null ? [] : (array) ($base['faculties'][(string) $facultyCode] ?? []);
        unset($base['faculties']);

        return new self(self::merge($base, $overrides));
    }

    public function academicSystem(): AcademicSystem
    {
        return AcademicSystem::fromConfig($this->get('academic_system'));
    }

    /**
     * الفصل الذي تُحفظ تحته توصية الطالب في res_recoms.
     * - semester: السلوك المعتاد (ملحق الفصل الأول يُسجَّل في الفصل الثاني).
     * - annual: فترة السنة الكاملة (3).
     * - semester_annual: لكل فصل نتيجته في الدور الأول، والملاحق والبدائل في نهاية السنة (3).
     */
    public function recomSemester(int $semester, int $examType): int
    {
        return match ($this->academicSystem()) {
            AcademicSystem::Annual => 3,
            AcademicSystem::SemesterAnnual => $examType === 2 ? 3 : $semester,
            AcademicSystem::Semester => ($semester === 1 && $examType === 2) ? 2 : $semester,
        };
    }

    /**
     * هل يُحسب دور الملاحق والبدائل على مستويات السنة كلها (نمط فصلي-سنوي)؟
     */
    public function combinesYearForSupplements(): bool
    {
        return $this->academicSystem() === AcademicSystem::SemesterAnnual;
    }

    /**
     * الفصل الافتراضي في نماذج الإدخال والطباعة: 3 للنظام السنوي، وإلا الفصل الأول.
     */
    public function defaultSemester(): int
    {
        return $this->academicSystem() === AcademicSystem::Annual ? 3 : 1;
    }

    /**
     * الفصول المتاحة عند تعريف المادة بحسب النظام (الفصلي الحالي يقتصر على الفصل الأول كما كان).
     *
     * @return list<int>
     */
    public function subjectSemesters(): array
    {
        return match ($this->academicSystem()) {
            AcademicSystem::Annual => [3],
            AcademicSystem::SemesterAnnual => [1, 2],
            AcademicSystem::Semester => [1],
        };
    }

    public function defaultPlan(): string
    {
        return (string) $this->get('default_plan', '20060001');
    }

    public function passMark(): int|float
    {
        return $this->get('pass_mark', 50);
    }

    public function supplementaryMaxMark(): int|float
    {
        return $this->get('supplementary.max_mark', 50);
    }

    public function supplementaryPassPoint(): float
    {
        return (float) $this->get('supplementary.pass_point', 2.0);
    }

    public function repeatAllAbovePct(): int|float
    {
        return $this->get('supplementary.repeat_all_above_pct', 70);
    }

    public function repeatSomeAbovePct(): int|float
    {
        return $this->get('supplementary.repeat_some_above_pct', 50);
    }

    public function removeFailMaxSubjects(): int
    {
        return (int) $this->get('supplementary.remove_fail_max_subjects', 1);
    }

    public function excludedReason(): int
    {
        return (int) $this->get('reasons.excluded', 21);
    }

    /**
     * @return list<int>
     */
    public function absentReasons(): array
    {
        return array_map('intval', (array) $this->get('reasons.absent_codes', [3001, 18]));
    }

    public function isAbsentReason(int|string|null $code): bool
    {
        return in_array((int) $code, $this->absentReasons(), true);
    }

    /**
     * كسر الساعات المسموح به قبل الإعادة أو الفصل.
     *
     * @param  'repeat_above'|'dismiss_above'  $rule
     * @return array{0: int, 1: int} [البسط، المقام]
     */
    public function failFraction(string $rule): array
    {
        $fraction = (array) $this->get("decisions.{$rule}", $rule === 'repeat_above' ? [1, 3] : [2, 3]);

        return [(int) ($fraction[0] ?? 1), max(1, (int) ($fraction[1] ?? 1))];
    }

    public function finalYearLevels(): int
    {
        return (int) $this->get('decisions.final_year_levels', 2);
    }

    /**
     * @return list<int>
     */
    public function repeatHistoryCodes(): array
    {
        return array_map('intval', (array) $this->get('decisions.repeat_history_codes', [2001, 2002]));
    }

    /**
     * @return list<int>
     */
    public function externalHistoryCodes(): array
    {
        return array_map('intval', (array) $this->get('decisions.external_history_codes', [4005]));
    }

    public function minGpa(): float
    {
        return (float) $this->get('graduation.min_gpa', 2.0);
    }

    /**
     * مراتب التصنيف من الأعلى للأدنى.
     *
     * @return list<array{min: float, general: string, honours: string}>
     */
    public function graduationBands(): array
    {
        return array_values(array_map(
            fn (array $band): array => ['min' => (float) $band['min'], 'general' => (string) $band['general'], 'honours' => (string) $band['honours']],
            (array) $this->get('graduation.bands', []),
        ));
    }

    /**
     * @return array{general: string, honours: string}
     */
    public function graduationFallback(): array
    {
        return [
            'general' => (string) $this->get('graduation.fallback.general', 'مقبول'),
            'honours' => (string) $this->get('graduation.fallback.honours', 'مرتبة الشرف الثالثة'),
        ];
    }

    public function absenceWarningPct(): int|float
    {
        return $this->get('absence.warning_pct', 15);
    }

    public function absenceDeprivationPct(): int|float
    {
        return $this->get('absence.deprivation_pct', 25);
    }

    public function countExcusedAsAbsent(): bool
    {
        return (bool) $this->get('absence.count_excused', true);
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * دمج التجاوزات: المصفوفات الترابطية تُدمج بالمفتاح، وغيرها (قوائم وقيم) تُستبدل كاملة.
     *
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function merge(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            $base[$key] = is_array($value) && is_array($base[$key] ?? null) && Arr::isAssoc($value) && Arr::isAssoc($base[$key])
                ? self::merge($base[$key], $value)
                : $value;
        }

        return $base;
    }
}
