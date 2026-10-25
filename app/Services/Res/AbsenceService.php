<?php

namespace App\Services\Res;

use App\Models\attendances\Attendance;
use App\Models\Res\AbsenceDecision;
use App\Models\User;
use App\Reg\AbsenceStatus;
use Carbon\CarbonInterface;
use DomainException;

/**
 * الحرمان بالغياب حسب اللائحة: إنذار عند 15% وحرمان مقترح عند تجاوز 25% من ساعات المقرر.
 * القرار النهائي للحرمان لمجلس الكلية بتوصية القسم، فالخدمة تقترح ولا تحرم آلياً.
 */
class AbsenceService
{
    /** @deprecated القيمة الفعلية: regulations.absence.warning_pct */
    public const WARNING_PCT = 15;

    /** @deprecated القيمة الفعلية: regulations.absence.deprivation_pct */
    public const DEPRIVATION_PCT = 25;

    /** @deprecated القيمة الفعلية: regulations.absence.count_excused */
    public const COUNT_EXCUSED_AS_ABSENT = true;

    /**
     * @return float نسبة الغياب مقرّبة لمنزلتين
     */
    public static function percentage(int $absent, int $total): float
    {
        return $total > 0 ? round(($absent / $total) * 100, 2) : 0.0;
    }

    public static function classify(float $pct): AbsenceStatus
    {
        $profile = RegulationProfile::current();

        return match (true) {
            $pct > $profile->absenceDeprivationPct() => AbsenceStatus::DeprivationProposed,
            $pct >= $profile->absenceWarningPct() => AbsenceStatus::Warning,
            default => AbsenceStatus::Normal,
        };
    }

    /**
     * عدد المحاضرات المسجّلة للطالب في المقرر وعدد غيابه ضمن الفترة.
     *
     * @return array{total: int, absent: int}
     */
    public function sessions(
        int|string $universityNo,
        int|string $subject,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
    ): array {
        $absentStatuses = RegulationProfile::current()->countExcusedAsAbsent() ? ['absent', 'excused'] : ['absent'];

        $query = Attendance::query()
            ->where('university_no', $universityNo)
            ->whereHas('lecture', fn ($q) => $q->where('subject_code', $subject))
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to));

        return [
            'total' => (clone $query)->count(),
            'absent' => (clone $query)->whereIn('status', $absentStatuses)->count(),
        ];
    }

    /**
     * يحسب الغياب ويحفظ القرار المقترح. لا يُعيد كتابة الحرمان المعتمد أو المرفوع بقرار المجلس.
     */
    public function evaluate(
        int|string $universityNo,
        int|string $subject,
        int|string $batch,
        int|string $semester,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
    ): AbsenceDecision {
        $counts = $this->sessions($universityNo, $subject, $from, $to);
        $pct = self::percentage($counts['absent'], $counts['total']);

        $decision = AbsenceDecision::query()->firstOrNew([
            'university_no' => $universityNo,
            'subject_code' => $subject,
            'batch_code' => $batch,
            'semester' => $semester,
        ]);

        $decision->fill([
            'total_sessions' => $counts['total'],
            'absent_sessions' => $counts['absent'],
            'absence_pct' => $pct,
        ]);

        if (! ($decision->status?->isDecided() ?? false)) {
            $status = self::classify($pct);

            if ($status === AbsenceStatus::Warning && $decision->warned_at === null) {
                $decision->warned_at = now();
            }

            $decision->status = $status;
        }

        $decision->save();

        return $decision;
    }

    /** اعتماد الحرمان بقرار مجلس الكلية (لا يكون إلا لمقترح تجاوز 25%) */
    public function approveDeprivation(AbsenceDecision $decision, User $user, ?string $note = null): AbsenceDecision
    {
        if ($decision->status !== AbsenceStatus::DeprivationProposed) {
            throw new DomainException('لا يُعتمد الحرمان إلا لمقرر تجاوز فيه الطالب 25% من الغياب.');
        }

        return $this->decide($decision, AbsenceStatus::Deprived, $user, $note);
    }

    /** رفع الحرمان (استثناء بقرار المجلس، ويلزم تدوين السبب) */
    public function lift(AbsenceDecision $decision, User $user, string $note): AbsenceDecision
    {
        if (! in_array($decision->status, [AbsenceStatus::DeprivationProposed, AbsenceStatus::Deprived], true)) {
            throw new DomainException('لا يُرفع إلا حرمان مقترح أو معتمد.');
        }

        if (trim($note) === '') {
            throw new DomainException('يلزم تدوين سبب رفع الحرمان.');
        }

        return $this->decide($decision, AbsenceStatus::Lifted, $user, $note);
    }

    /** هل يُمنع الطالب من الجلوس للامتحان النهائي في هذا المقرر؟ */
    public function isBarredFromFinalExam(int|string $universityNo, int|string $subject, int|string $batch, int|string $semester): bool
    {
        return AbsenceDecision::query()
            ->where([
                'university_no' => $universityNo,
                'subject_code' => $subject,
                'batch_code' => $batch,
                'semester' => $semester,
                'status' => AbsenceStatus::Deprived->value,
            ])
            ->exists();
    }

    private function decide(AbsenceDecision $decision, AbsenceStatus $status, User $user, ?string $note): AbsenceDecision
    {
        $decision->forceFill([
            'status' => $status,
            'decided_by' => $user->getKey(),
            'decided_at' => now(),
            'note' => $note,
        ])->save();

        return $decision;
    }
}
