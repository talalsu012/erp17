<?php

namespace App\Helpers;

use App\Models\Cert\CertSetting;
use App\Models\Cert\CertStudent;
use App\Models\Reg\Batch;
use App\Models\Reg\Student;
use App\Models\Res\Mark;
use App\Models\Res\RecordMark;

class TranscriptGenerator
{
    /**
     * Generate transcript data for a student
     *
     * @param  int  $universityNo
     * @return array
     */
    public static function generateTranscript($universityNo, $lang = 'ar')
    {
        // Get student information
        $student = CertStudent::where('universityno', $universityNo)
            ->with([
                'degree',
                'grade',
                'faculty',
                'admission',
                'nationalty',
            ])
            ->first();

        if (! $student) {
            return ['error' => 'Student not found'];
        }
        // Generate or update the verify hash
        //  $student->Verify();

        $marks = self::marksFor($universityNo);

        // Organize marks by academic year
        $transcriptData = [];
        $totalCreditHours = 0;

        foreach ($marks as $mark) {
            $levelCode = $mark->level_code;
            $batchCode = $mark->batch_code;

            if (! isset($transcriptData[$levelCode])) {
                $transcriptData[$levelCode] = [
                    'level_name' => $lang === 'ar' ? $mark->level->name ?? 'N/A' : $mark->level->name_en ?? 'N/A',
                    'batch_name' => $lang === 'ar' ? $mark->batch->name ?? 'N/A' : $mark->batch->name_en ?? 'N/A',
                    'year' => $mark->batch_code ?? null,
                    'subjects' => [],
                    'year_grade' => self::calculateYearGrade($marks->where('level_code', $levelCode), $lang),
                ];
            }

            $transcriptData[$levelCode]['subjects'][] = [
                'name' => $lang === 'ar'
                    ? $mark->subject_name_ar ?? $mark->subject->name_ar ?? 'N/A'
                    : $mark->subject_name_en ?? $mark->subject->name_en ?? 'N/A',
                'credit_hours' => $mark->crch ?? $mark->subject->crch ?? 0,
                'grade' => $lang === 'ar' ? $mark->grade ?? 'N/A' : self::convertToGrade($mark->mark, $lang) ?? $mark->mark,
                'year' => $mark->batch->name ?? null,
            ];

            $totalCreditHours += $mark->crch ?? $mark->subject->crch ?? 0;
        }
        $academic_affairs = CertSetting::where('is_visible', true)
            ->where('groups', 'academic_affairs')
            ->first();
        $registrar_faculty = CertSetting::where('is_visible', true)
            ->where('groups', 'registrar_faculty')
            ->first();
        $dean_faculty = CertSetting::where('is_visible', true)
            ->where('groups', 'dean_faculty')
            ->first();
        $split_faculty = CertSetting::where('is_visible', true)
            ->where('groups', 'split_faculty')
            ->first();

        return [
            'student' => [
                'name' => $lang === 'ar' ? $student->name_ar : $student->name_en,
                'universityno' => $student->universityno,
                'faculty_code' => $student->faculty_code,
                'split_faculty' => $lang === 'ar' ? $split_faculty->name_ar ?? 'N/A' : $split_faculty->name_en ?? 'N/A',
                'faculty' => $lang === 'ar' ? $student->faculty->name ?? 'N/A' : $student->faculty->name_en ?? 'N/A',
                'alumia_date' => $student->alumia_date ? $student->alumia_date->format('d/m/Y') : 'N/A',
                'degree' => $lang === 'ar' ? $student->degree->name_ar ?? 'N/A' : $student->degree->name_en ?? 'N/A',
                'class' => $lang === 'ar' ? $student->grade->name_ar ?? 'N/A' : $student->grade->name_en ?? 'N/A',
                'total_credit_hours' => $totalCreditHours,
                'nationality' => $lang === 'ar' ? $student->nationalty->name ?? 'N/A' : $student->nationalty->name_en ?? 'N/A',
                'admission' => $student->admission
                    ? ''.($lang === 'ar'
                        ? $student->admission->name_ar
                        : $student->admission->name_en
                    ).''
                    : '',
            ],
            'transcript' => $transcriptData,
            'grade_scale' => self::getGradeScale($lang),
            'academic_affairs' => $lang === 'ar' ? $academic_affairs->name_ar ?? 'N/A' : $academic_affairs->name_en ?? 'N/A',
            'registrar_faculty' => $lang === 'ar' ? $registrar_faculty->name_ar ?? 'N/A' : $registrar_faculty->name_en ?? 'N/A',
            'dean_faculty' => $lang === 'ar' ? $dean_faculty->name_ar ?? 'N/A' : $dean_faculty->name_en ?? 'N/A',
            'lang' => $lang,

        ];
    }

    /**
     * درجات الشهادة: من السجل الأكاديمي المعتمد (لقطة ثابتة موقّعة)، وبحسب الإعداد
     * `results.certificates_from_record_only` قد تُستعمل الدرجات الحالية لمن ليس له سجل معتمد.
     *
     * @return \Illuminate\Support\Collection<int, RecordMark|Mark>
     */
    public static function marksFor($universityNo)
    {
        $approved = RecordMark::approvedFor($universityNo)
            ->with(['level', 'batch'])
            ->orderBy('level_code')
            ->orderBy('batch_code')
            ->orderBy('semester')
            ->get();

        if ($approved->isNotEmpty() || config('results.certificates_from_record_only')) {
            return $approved;
        }

        return Mark::where('university_no', $universityNo)
            ->where('is_visible', 1)
            ->with(['subject', 'level', 'batch'])
            ->orderBy('level_code')
            ->orderBy('batch_code')
            ->orderBy('semester')
            ->get();
    }

    /**
     * Convert numerical mark to grade
     *
     * @param  int  $mark
     * @return string
     */
    private static function convertToGrade($mark, $lang = 'ar')
    {
        if ($mark >= 80) {
            return $lang === 'ar' ? 'ممتاز' : 'Excellent';
        }
        if ($mark >= 70) {
            return $lang === 'ar' ? 'جيد جداً' : 'Very Good';
        }
        if ($mark >= 60) {
            return $lang === 'ar' ? 'جيد' : 'Good';
        }
        if ($mark >= 50) {
            return $lang === 'ar' ? 'نجاح' : 'Pass';
        }

        return $lang === 'ar' ? 'راسب' : 'Fail';
    }

    /**
     * Calculate year grade based on marks
     *
     * @param  Illuminate\Support\Collection  $yearMarks
     * @return string
     */
    private static function calculateYearGrade($yearMarks, $lang = 'ar')
    {
        $total = 0;
        $count = $yearMarks->count();

        if ($count === 0) {
            return 'N/A';
        }

        foreach ($yearMarks as $mark) {
            $total += $mark->mark;
        }

        $average = $total / $count;

        return self::convertToGrade($average, $lang);
    }

    /**
     * Calculate overall class based on all marks
     *
     * @param  Illuminate\Support\Collection  $allMarks
     * @return string
     */
    private static function calculateOverallClass($allMarks, $lang = 'ar')
    {
        $total = 0;
        $count = $allMarks->count();

        if ($count === 0) {
            return 'N/A';
        }

        foreach ($allMarks as $mark) {
            $total += $mark->mark;
        }

        $average = $total / $count;

        if ($average >= 70) {
            return $lang === 'ar' ? 'الدرجة الأولى' : 'First Class';
        }
        if ($average >= 60) {
            return $lang === 'ar' ? 'الدرجة الثانية - القسم الأول' : 'Second Class-Division One';
        }
        if ($average >= 50) {
            return $lang === 'ar' ? 'الدرجة الثانية - القسم الثاني' : 'Second Class-Division Two';
        }

        return $lang === 'ar' ? 'الدرجة الثالثة' : 'Third Class';
    }

    /**
     * Get academic year from batch start date
     *
     * @param  string  $startDate
     * @return string
     */
    private static function getAcademicYear($startDate)
    {
        if (! $startDate) {
            return 'N/A';
        }

        $year = date('Y', strtotime($startDate));

        return $year.'/'.($year + 1);
    }

    /**
     * Return grade scale information
     *
     * @return array
     */
    private static function getGradeScale($lang = 'ar')
    {
        return [
            ['range' => '80 - 100', 'grade' => $lang === 'ar' ? 'ممتاز' : 'Excellent'],
            ['range' => '70 - 79.99', 'grade' => $lang === 'ar' ? 'جيد جداً' : 'Very Good'],
            ['range' => '60 - 69.99', 'grade' => $lang === 'ar' ? 'جيد' : 'Good'],
            ['range' => '50 - 59.99', 'grade' => $lang === 'ar' ? 'نجاح' : 'Pass'],
            ['range' => $lang === 'ar' ? 'نجاح بعد الامتحان التكميلي' : 'Pass after supp.', 'grade' => $lang === 'ar' ? 'مقبول*' : 'Pass*'],
        ];
    }
}
