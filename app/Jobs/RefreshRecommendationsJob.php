<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\Res\RegulationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshRecommendationsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public int $tries = 1;

    public function __construct(
        public string $batch,
        public string $faculty,
        public string $department,
        public ?string $special,
        public string $level,
        public string $semester,
        public string $examType,
        public ?string $universityNo = null,
    ) {}

    public function handle(RegulationService $regulation): void
    {
        $query = Student::query()
            ->where('batch_code', $this->batch)
            ->where('faculty_code', $this->faculty)
            ->where('department_code', $this->department)
            ->where('level_code', $this->level)
            ->when(
                filled($this->special),
                fn ($query) => $query->where('special_code', $this->special)
            )
            ->when(
                filled($this->universityNo),
                fn ($query) => $query->where('university_no', $this->universityNo)
            )
            ->select(['university_no', 'department_code', 'special_code'])
            ->orderBy('university_no');

        $total = (clone $query)->count();

        Log::info('RefreshRecommendationsJob started', [
            'batch' => $this->batch,
            'faculty' => $this->faculty,
            'department' => $this->department,
            'special' => $this->special,
            'level' => $this->level,
            'semester' => $this->semester,
            'examType' => $this->examType,
            'university_no' => $this->universityNo,
            'students_count' => $total,
        ]);

        $updated = 0;
        $failed = 0;

        /*
         * university_no هو الحقل المستخدم للـ chunk
         * بدلاً من id.
         */
        $query->chunkById(
            100,
            function ($students) use ($regulation, &$updated, &$failed) {

                foreach ($students as $student) {

                    try {
                        $regulation->refresh(
                            $student,
                            $this->faculty,
                            $this->level,
                            $this->batch,
                            $this->semester,
                            $this->examType,
                        );

                        $updated++;

                    } catch (Throwable $e) {

                        $failed++;

                        Log::error(
                            'Failed to refresh recommendation',
                            [
                                'university_no' => $student->university_no,

                                'batch' => $this->batch,

                                'faculty' => $this->faculty,

                                'level' => $this->level,

                                'semester' => $this->semester,

                                'examType' => $this->examType,

                                'error' => $e->getMessage(),

                                'file' => $e->getFile(),

                                'line' => $e->getLine(),
                            ]
                        );
                    }
                }
            },
            column: 'university_no'
        );

        Log::info('RefreshRecommendationsJob completed', [
            'total' => $total,
            'updated' => $updated,
            'failed' => $failed,
        ]);
    }

    /**
     * يتم استدعاؤها إذا فشل الـ Job نفسه بالكامل.
     */
    public function failed(?Throwable $exception): void
    {
        Log::critical('RefreshRecommendationsJob failed completely', [
            'batch' => $this->batch,
            'faculty' => $this->faculty,
            'department' => $this->department,
            'level' => $this->level,
            'error' => $exception?->getMessage(),
        ]);
    }
}
