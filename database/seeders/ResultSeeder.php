<?php

namespace Database\Seeders;

use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use App\Models\Student;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    private const SUBJECTS_PER_STUDENT = 8;

    /**
     * درجات مقررات الطالب (فصله ومستواه وقسمه) ثم التوصية الفصلية المحسوبة منها.
     */
    public function run(): void
    {
        Student::query()->each(function (Student $student): void {
            $subjects = Subject::query()
                ->where('faculty_code', $student->faculty_code)
                ->where('department_code', $student->department_code)
                ->where('level_code', $student->level_code)
                ->where('is_visible', true)
                ->orderBy('code')
                ->get();

            $semester = $subjects->first()?->semester_id;

            $marks = $subjects
                ->filter(fn (Subject $subject): bool => $subject->semester_id === $semester)
                ->take(self::SUBJECTS_PER_STUDENT)
                ->map(fn (Subject $subject): Mark => Mark::factory()->forStudentSubject($student, $subject)->create());

            if ($marks->isNotEmpty()) {
                Recom::factory()->forStudentMarks($student, $marks)->create();
            }
        });
    }
}
