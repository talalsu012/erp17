<?php

namespace Database\Seeders;

use App\Models\Res\Grade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * سلّم تقديرات لائحة 2020 للخطتين (بكالريوس 20060001، دبلوم 20060002).
 * يستبدل جدول res_grades فقط بعد حفظ نسخة JSON من القيم الحالية في storage/app/regulation-backups.
 * لا يمس res_marks: الدرجات القائمة تُعاد حسابها بالأمر res:recalculate-grades.
 */
class RegulationGradesSeeder extends Seeder
{
    /** @var array<string, list<array{0: int, 1: int, 2: string, 3: string, 4: float}>> [lower, upper, title, titlear, point] */
    public const SCALES = [
        '20060001' => [
            [80, 100, 'A', 'أ', 4.0],
            [75, 79, '+B', 'ب+', 3.5],
            [70, 74, 'B', 'ب', 3.0],
            [60, 69, '+C', 'ج+', 2.5],
            [50, 59, 'C', 'ج', 2.0],
            [0, 49, 'F', 'ر', 0.0],
        ],
        '20060002' => [
            [80, 100, 'A', 'أ', 4.0],
            [70, 79, '+B', 'ب+', 3.5],
            [60, 69, 'B', 'ب', 3.0],
            [40, 59, 'C', 'ج', 2.0],
            [0, 39, 'F', 'ر', 0.0],
        ],
    ];

    public function run(): void
    {
        $plans = array_keys(self::SCALES);

        $backup = Grade::withTrashed()->whereIn('plan', $plans)->get()->toArray();
        $path = 'regulation-backups/res_grades_'.now()->format('Ymd_His').'.json';
        Storage::disk('local')->put($path, json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        DB::transaction(function () use ($plans) {
            Grade::withTrashed()->whereIn('plan', $plans)->get()->each->forceDelete();

            foreach (self::SCALES as $plan => $rows) {
                foreach ($rows as [$lower, $upper, $title, $titleAr, $point]) {
                    Grade::create([
                        'plan' => $plan,
                        'lower' => $lower,
                        'upper' => $upper,
                        'title' => $title,
                        'titlear' => $titleAr,
                        'point' => $point,
                    ]);
                }
            }
        });

        $this->command?->info("حُفظت النسخة الاحتياطية: storage/app/private/{$path}");
    }
}
