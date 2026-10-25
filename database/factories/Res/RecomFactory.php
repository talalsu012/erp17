<?php

namespace Database\Factories\Res;

use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Recom>
 */
class RecomFactory extends Factory
{
    private const RECOM_PASS = 1005;

    private const RECOM_SUPPLEMENTARY = 4001;

    private const RECOM_REPEAT_SEMESTER = 2002;

    private const MAX_SUPPLEMENTARY_FAILS = 2;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_no' => Student::factory(),
            'semester' => '1',
            'examType' => '1',
            'is_visible' => true,
            'is_locked' => false,
            'is_released' => false,
        ];
    }

    /**
     * @param  Collection<int, Mark>  $marks
     */
    public function forStudentMarks(Student $student, Collection $marks): static
    {
        return $this->state(function () use ($student, $marks): array {
            $creditHours = Subject::query()->whereIn('code', $marks->pluck('subject_code'))->pluck('crch', 'code');
            $failed = $marks->filter(fn (Mark $mark): bool => (float) $mark->point <= 0);
            $totalCreditHours = (int) $marks->sum(fn (Mark $mark): int => (int) $creditHours[$mark->subject_code]);
            $points = $marks->sum(fn (Mark $mark): float => (float) $mark->point * (int) $creditHours[$mark->subject_code]);
            $gpa = $totalCreditHours > 0 ? round($points / $totalCreditHours, 2) : 0.0;

            return [
                'university_no' => $student->university_no,
                'faculty_code' => $student->faculty_code,
                'department_code' => $student->department_code,
                'special_code' => $student->special_code,
                'level_code' => $student->level_code,
                'batch_code' => $student->batch_code,
                'semester' => $marks->first()->semester,
                'countSubject' => $marks->count(),
                'sumMarks' => (int) $marks->sum('mark'),
                's_pass' => $marks->count() - $failed->count(),
                's_fail' => $failed->count(),
                'crch_fail' => (int) $failed->sum(fn (Mark $mark): int => (int) $creditHours[$mark->subject_code]),
                's_abs' => 0,
                'crch' => $totalCreditHours,
                'points' => $points,
                'gpa' => $gpa,
                'cgpa' => $gpa,
                'recom_code' => match (true) {
                    $failed->isEmpty() => self::RECOM_PASS,
                    $failed->count() <= self::MAX_SUPPLEMENTARY_FAILS => self::RECOM_SUPPLEMENTARY,
                    default => self::RECOM_REPEAT_SEMESTER,
                },
            ];
        });
    }
}
