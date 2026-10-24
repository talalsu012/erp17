<?php

namespace App\Console\Commands\Res;

use App\Models\attendances\Attendance;
use App\Reg\AbsenceStatus;
use App\Services\Res\AbsenceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EvaluateAbsence extends Command
{
    protected $signature = 'res:evaluate-absence
        {--batch= : حصر الحساب في دفعة (batch_code) محددة}
        {--from= : بداية فترة الحضور (Y-m-d)}
        {--to= : نهاية فترة الحضور (Y-m-d)}';

    protected $description = 'يحسب نسبة غياب كل طالب في كل مقرر وينشئ الإنذارات (15%) والحرمان المقترح (أكثر من 25%)';

    public function handle(AbsenceService $service): int
    {
        $from = $this->option('from') ? Carbon::parse($this->option('from')) : null;
        $to = $this->option('to') ? Carbon::parse($this->option('to')) : null;

        $counts = array_fill_keys(array_column(AbsenceStatus::cases(), 'value'), 0);

        $pairs = Attendance::query()
            ->join('att_lectures', 'att_lectures.id', '=', 'att_attendances.lecture_id')
            ->join('students', 'students.university_no', '=', 'att_attendances.university_no')
            ->join('subjects', 'subjects.code', '=', 'att_lectures.subject_code')
            ->whereNotNull('students.batch_code')
            ->whereNotNull('subjects.semester_id')
            ->when($this->option('batch'), fn ($q, $batch) => $q->where('students.batch_code', $batch))
            ->when($from, fn ($q) => $q->whereDate('att_attendances.date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('att_attendances.date', '<=', $to))
            ->select([
                'att_attendances.university_no',
                'att_lectures.subject_code',
                'students.batch_code',
                'subjects.semester_id',
            ])
            ->distinct();

        $total = 0;

        $pairs->orderBy('att_attendances.university_no')
            ->each(function ($row) use ($service, $from, $to, &$counts, &$total) {
                $decision = $service->evaluate(
                    $row->university_no,
                    $row->subject_code,
                    $row->batch_code,
                    $row->semester_id,
                    $from,
                    $to,
                );

                $counts[$decision->status->value]++;
                $total++;
            }, 500);

        $this->info("تم تقييم {$total} سجل (طالب/مقرر).");

        foreach (AbsenceStatus::cases() as $status) {
            $this->line(sprintf('  %-22s %d', $status->getLabel(), $counts[$status->value]));
        }

        return self::SUCCESS;
    }
}
