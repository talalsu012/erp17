<?php

namespace App\Services\Reg;

use App\Models\Reg\OpenReg;
use App\Models\Student;
use App\Reg\RegAccess;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * هل التسجيل مفتوح للطالب؟ مصدر الفتح والقفل الوحيد هو open-regs (كلية/قسم/مستوى/دفعة + تاريخا البداية والنهاية)،
 * ويُخصَّص للطالب الواحد بالحقل reg_access: open يفتحه رغم إغلاق الفترة، وclosed يحجبه رغم فتحها.
 * الحقول الفارغة في صف open-regs (القسم/المستوى/الدفعة) تعني «الكل».
 */
class RegistrationAccessService
{
    /**
     * صفوف open-regs التي تنطبق على الطالب (بأي تاريخ).
     *
     * @param  Collection<int, OpenReg>|null  $windows  صفوف الكلية محمّلة مسبقاً لتفادي الاستعلام (للعمليات الجماعية)
     * @return Collection<int, OpenReg>
     */
    public function windowsFor(Student $student, ?Collection $windows = null): Collection
    {
        $windows ??= OpenReg::query()->where('faculty_code', $student->faculty_code)->get();

        return $windows
            ->filter(fn (OpenReg $window): bool => $this->matches($window, $student))
            ->values();
    }

    public function isOpen(Student $student, ?CarbonInterface $on = null, ?Collection $windows = null): bool
    {
        if ($student->reg_access === RegAccess::Closed) {
            return false;
        }

        if ($student->reg_access === RegAccess::Open) {
            return true;
        }

        $today = ($on ?? now())->toDateString();

        return $this->windowsFor($student, $windows)->contains(
            fn (OpenReg $window): bool => $this->contains($window->start_date?->toDateString(), $window->end_date?->toDateString(), $today)
        );
    }

    /**
     * الفترات التي تُحتسب مدفوعات الطالب فيها (بداية/نهاية، والفارغ يعني بلا حد):
     * فترات open-regs المنطبقة، وللمفتوح له التسجيل فردياً فترة مفتوحة من تاريخ المنح.
     *
     * @param  Collection<int, OpenReg>|null  $windows
     * @return list<array{0: string|null, 1: string|null}>
     */
    public function periodsFor(Student $student, ?Collection $windows = null): array
    {
        if ($student->reg_access === RegAccess::Closed) {
            return [];
        }

        $periods = $this->windowsFor($student, $windows)
            ->map(fn (OpenReg $window): array => [$window->start_date?->toDateString(), $window->end_date?->toDateString()])
            ->all();

        if ($student->reg_access === RegAccess::Open && $student->reg_access_at !== null) {
            $periods[] = [$student->reg_access_at->toDateString(), null];
        }

        return $periods;
    }

    /**
     * يضبط تخصيص الطالب: open أو closed، وnull يعيده إلى فترة open-regs.
     */
    public function grant(Student $student, ?RegAccess $access): Student
    {
        $student->forceFill([
            'reg_access' => $access,
            'reg_access_at' => $access === RegAccess::Open ? now() : null,
        ])->save();

        return $student;
    }

    private function matches(OpenReg $window, Student $student): bool
    {
        return (string) $window->faculty_code === (string) $student->faculty_code
            && (blank($window->department_code) || (string) $window->department_code === (string) $student->department_code)
            && (blank($window->level_code) || (string) $window->level_code === (string) $student->level_code)
            && (blank($window->batch_code) || (string) $window->batch_code === (string) $student->batch_code);
    }

    private function contains(?string $start, ?string $end, string $date): bool
    {
        return ($start === null || $start <= $date) && ($end === null || $end >= $date);
    }
}
