<?php

namespace App\Helpers;

use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Student;
use App\Reg\ShowAbsentCode;
use Illuminate\Support\Facades\Cache;

class StudentHelper
{
    /**
     * Get subject details by subject code
     */
    public static function getStudentDetails(string $university_no): ?array
    {
        if (! $university_no) {
            return null;
        }
        $student = Cache::remember("student_{$university_no}", 60, fn () => Student::with(['faculty', 'department', 'batch', 'special'])->find($university_no));

        return [
            'name_ar' => $student->name_ar,
            'name_en' => $student->name_en,
            'faculty_code' => $student->faculty->code ?? null,
            'faculty_name' => $student->faculty->name_ar ?? null,
            'department_code' => $student->department->code ?? null,
            'department_name' => $student->department->name_ar ?? null,
            'special_code' => $student->special->code ?? null,
            'special_name' => $student->special->name_ar ?? null,
            'batch_code' => $student->batch->code ?? null,
            'batch_name' => $student->batch->name ?? null,
            'university_no' => $student->university_no,
            'is_active' => $student->is_active,
            'stady_code' => $student->stady_code,
            'status_code' => $student->status_code,
            'xstatus' => $student->xstatus,
        ];
    }

    public static function getPreviousRepeatCount(string $university_no, string $batch_code): int
    {
        return Recom::where('university_no', $university_no)
            ->whereIn('recom_code', ShowAbsentCode::Repeat())
            ->where('batch_code', '<>', $batch_code)
            ->count();
    }

    public static function getPreviousExternalExamCount(string $university_no, string $batch_code): int
    {
        return Recom::where('university_no', $university_no)
            ->whereIn('recom_code', ShowAbsentCode::External())
            ->where('batch_code', '<>', $batch_code)
            ->count();
    }

    public static function getSubjectDetails(string $subject_code): ?array
    {
        $subject = Cache::remember("subject_{$subject_code}", 60, fn () => Mark::where('subject_code', $subject_code)->first());

        if (! $subject) {
            return null;
        }

        return [
            'code' => $subject->code,
            'name' => $subject->name_ar,
            'crch' => $subject->crch,
            'semester' => $subject->semester,
            'is_visible' => $subject->is_visible,
        ];
    }

    /**
     * Get student's current semester based on their marks
     */
    public static function getCurrentSemester(string $university_no): ?string
    {
        $latestMark = Mark::where('university_no', $university_no)
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestMark ? $latestMark->semester : null;
    }

    /**
     * Get student's active subjects
     */
    public static function getActiveSubjects(string $university_no): array
    {
        $student = Student::with('subjects')->find($university_no);

        return $student->subjects->filter(fn ($subject) => $subject->is_visible && $subject->status === 'active')->toArray();
    }

    /**
     * Calculate student's total credit hours
     */
    public static function calculateTotalCrch(string $university_no): float
    {
        $subjects = self::getActiveSubjects($university_no);

        return array_reduce($subjects, fn ($carry, $subject) => $carry + ($subject['crch'] ?? 0), 0);
    }

    /**
     * Get student's batch information
     */
    public static function getBatchInfo(string $university_no): array
    {
        $student = Student::with('batch')->find($university_no);

        return [
            'code' => $student->batch->code ?? null,
            'name' => $student->batch->name ?? null,
            'start_date' => $student->batch->start_date ?? null,
            'end_date' => $student->batch->end_date ?? null,
        ];
    }

    /**
     * Check if student has any failed subjects
     */
    public static function hasFailedSubjects(string $university_no): bool
    {
        $marksFail = 50;
        $stdudentsDetials = self::getStudentDetails($university_no);
        if ($stdudentsDetials['stady_code'] == 232) {
            $marksFail = 40;
        }

        return Mark::where('university_no', $university_no)
            ->where('mark', '<', $marksFail) // Assuming 50 is passing grade
            ->exists();
    }

    /**
     * Get student's faculty and department info
     */
    public static function getStudentDepartmentInfo(string $university_no): array
    {
        $student = Student::with(['faculty', 'department'])->find($university_no);

        return [
            'faculty_code' => $student->faculty->code ?? null,
            'faculty_name' => $student->faculty->name_ar ?? null,
            'department_code' => $student->department->code ?? null,
            'department_name' => $student->department->name_ar ?? null,
        ];
    }

    /**
     * Get student's payment status summary
     */
    public static function getPaymentStatus(string $university_no): array
    {
        $student = Student::find($university_no);

        return [
            'total_fees' => $student->all_fees ?? 0,
            'paid_amount' => $student->pay ?? 0,
            'remaining_amount' => ($student->all_fees ?? 0) - ($student->pay ?? 0),
            'installment_plan' => $student->installment->title_ar ?? null,
            'discount_percent' => $student->discount_percent ?? 0,
        ];
    }

    /**
     * Get student's attendance summary
     */
    public static function getAttendanceSummary(string $university_no): array
    {
        $student = Student::withCount([
            'lectures as total_lectures',
            'lectures as attended_lectures' => function ($query) {
                $query->where('status', 'present');
            },
        ])->find($university_no);

        return [
            'total_lectures' => $student->total_lectures ?? 0,
            'attended_lectures' => $student->attended_lectures ?? 0,
            'attendance_percentage' => $student->total_lectures > 0
                ? round(($student->attended_lectures / $student->total_lectures) * 100, 2)
                : 0,
        ];
    }
}
