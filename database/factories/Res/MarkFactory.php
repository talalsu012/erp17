<?php

namespace Database\Factories\Res;

use App\Models\Res\Grade;
use App\Models\Res\Mark;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Reg\MarkApprovalStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mark>
 */
class MarkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_no' => Student::factory(),
            'semester' => '1',
            'examType' => '1',
            'mark' => $this->faker->numberBetween(35, 98),
            'cwork' => null,
            'approval_stage' => MarkApprovalStage::Professor->value,
            'is_visible' => true,
            'is_locked' => false,
            'is_released' => false,
            'is_recheck' => false,
            'is_recheck_passed' => false,
        ];
    }

    public function forStudentSubject(Student $student, Subject $subject): static
    {
        return $this->state(function (array $attributes) use ($student, $subject): array {
            $mark = (int) $attributes['mark'];
            $grade = Grade::query()
                ->where('plan', $student->stady_code)
                ->where('lower', '<=', $mark)
                ->where('upper', '>=', $mark)
                ->first();

            return [
                'university_no' => $student->university_no,
                'subject_code' => $subject->code,
                'faculty_code' => $student->faculty_code,
                'department_code' => $student->department_code,
                'special_code' => $student->special_code,
                'level_code' => $student->level_code,
                'batch_code' => $student->batch_code,
                'semester' => $subject->semester_id ?? '1',
                'grade' => $grade?->title,
                'point' => $grade?->point,
            ];
        });
    }
}
