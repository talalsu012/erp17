<?php

namespace App\Console\Commands\Res;

use App\Models\Res\Mark;
use App\Models\Student;
use App\Services\Res\MarkEntryService;
use App\Services\Res\RegulationService;
use DomainException;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecalculateGrades extends Command
{
    protected $signature = 'res:recalculate-grades
        {--plan= : حصر الحساب في خطة (stady_code) محددة}
        {--apply : تنفيذ التعديل فعلياً (الافتراضي: عرض الفروق فقط)}
        {--refresh-from= : إعادة حساب التوصيات فقط للدرجات الواردة في ملف نسخة احتياطية سابق (مسار نسبي لـ storage/app/private)}';

    protected $description = 'يعيد حساب تقدير ونقاط الدرجات غير المقفلة حسب سلّم res_grades الحالي، ثم توصيات الطلاب المتأثرين';

    public function handle(MarkEntryService $entry, RegulationService $regulation): int
    {
        if ($file = $this->option('refresh-from')) {
            return $this->refreshFromBackup($regulation, (string) $file);
        }

        $apply = (bool) $this->option('apply');
        $changes = [];
        $skipped = 0;
        $groups = [];

        Mark::query()
            ->where('is_locked', false)
            ->whereNotNull('mark')
            ->whereNull('reson_code')
            ->whereHas('student', fn ($q) => $q->when($this->option('plan'), fn ($q, $plan) => $q->where('stady_code', $plan)))
            ->with('student')
            ->chunkById(1000, function ($marks) use ($entry, &$changes, &$skipped, &$groups) {
                foreach ($marks as $mark) {
                    /** @var Student $student */
                    $student = $mark->student;

                    try {
                        [$grade, $point] = $entry->gradeFor($mark, $student, (int) $mark->examType, 0);
                    } catch (DomainException) {
                        $skipped++;

                        continue;
                    }

                    if (trim((string) $mark->grade) === trim((string) $grade) && (float) $mark->point === (float) $point) {
                        continue;
                    }

                    $changes[$mark->id] = [
                        'id' => $mark->id,
                        'university_no' => $mark->university_no,
                        'old' => [$mark->grade, $mark->point],
                        'new' => [$grade, $point],
                        'pointChanged' => (float) $mark->point !== (float) $point,
                    ];

                    $groups[implode('|', [$mark->university_no, $mark->faculty_code, $mark->level_code, $mark->batch_code, $mark->semester, $mark->examType])] = true;
                }
            });

        $pointChanges = count(array_filter($changes, fn ($c) => $c['pointChanged']));
        $this->info('درجات تتغير: '.count($changes)." (منها تتغير نقاطها: {$pointChanges}) ، تُخطّى بلا تقدير مطابق: {$skipped}");
        $this->info('توصيات ستُعاد: '.count($groups));

        if (! $apply) {
            $this->warn('عرض فقط. أضف --apply للتنفيذ (تُحفظ نسخة احتياطية أولاً).');

            return self::SUCCESS;
        }

        $path = 'regulation-backups/res_marks_'.now()->format('Ymd_His').'.json';
        Storage::disk('local')->put($path, json_encode(array_values($changes), JSON_UNESCAPED_UNICODE));
        $this->info("النسخة الاحتياطية: storage/app/private/{$path}");

        DB::transaction(function () use ($changes) {
            foreach (array_chunk($changes, 500, true) as $chunk) {
                foreach ($chunk as $change) {
                    Mark::withoutEvents(fn () => Mark::query()->whereKey($change['id'])->where('is_locked', false)->update([
                        'grade' => $change['new'][0],
                        'point' => $change['new'][1],
                    ]));
                }
            }
        });

        $this->recalculateRecoms($regulation, array_keys($groups));

        return self::SUCCESS;
    }

    private function refreshFromBackup(RegulationService $regulation, string $file): int
    {
        $ids = collect(json_decode(Storage::disk('local')->get($file), true))->pluck('id');

        $keys = Mark::query()
            ->whereIn('id', $ids)
            ->get(['university_no', 'faculty_code', 'level_code', 'batch_code', 'semester', 'examType'])
            ->map(fn (Mark $m) => implode('|', [$m->university_no, $m->faculty_code, $m->level_code, $m->batch_code, $m->semester, $m->examType]))
            ->unique()
            ->values()
            ->all();

        $this->info('توصيات ستُعاد: '.count($keys));
        $this->recalculateRecoms($regulation, $keys);

        return self::SUCCESS;
    }

    /** @param  list<string>  $keys */
    private function recalculateRecoms(RegulationService $regulation, array $keys): void
    {
        $failed = [];
        $bar = $this->output->createProgressBar(count($keys));

        foreach ($keys as $key) {
            [$universityNo, $faculty, $level, $batch, $semester, $examType] = explode('|', $key);

            $student = Student::query()->where('university_no', $universityNo)->first();

            if ($student) {
                try {
                    $regulation->refresh($student, $faculty, $level, $batch, $semester, $examType);
                } catch (QueryException $e) {
                    $failed[] = "{$universityNo} ({$level}/{$batch}/{$semester}/{$examType}): ".$e->errorInfo[2];
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($failed !== []) {
            $this->warn('تعذّر تحديث '.count($failed).' توصية (غالباً بيانات القسم/الكلية للطالب غير صالحة):');
            collect($failed)->take(15)->each(fn (string $line) => $this->line("  {$line}"));
        }
    }
}
