<?php

namespace App\Helpers\Res\Services;

use App\Helpers\StudentHelper;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Subject;
use Illuminate\Support\Facades\Log;

class MarkCalculationService
{
    private array $studentDetails;

    public function __construct(
        private readonly string $university_no,
        private readonly string $faculty_code,
        private readonly string $level_code,
        private readonly string $batch_code,
        private readonly string $semester,
        private readonly string $examType
    ) {
        $this->studentDetails = StudentHelper::getStudentDetails($this->university_no);

    }

    /**
     * حساب إحصائيات الطالب
     */
    public function calculateStatistics($Fianal = \false): array
    {
        $subjects = $this->getSubjects($Fianal);
        $marks = $this->getStudentMarks($Fianal);

        $stats = [
            'countSubject' => $subjects->count(),
            'sumMarks' => 0,
            's_pass' => 0,
            's_fail' => 0,
            'crch_fail' => 0,
            's_abs' => 0,
            'crch' => 0,
            'points' => 0,
            'gpa' => 0,
        ];

        $uniqueSubjects = [];
        $marksFail = $this->getPassingMark();

        foreach ($marks as $markRecord) {
            $stats['sumMarks'] += $markRecord->mark;

            // تحديد حالة المادة (ناجح/راسب/غائب)
            if ($markRecord->reson_code == 4001) {
                $stats['s_abs']++;
            } elseif ($markRecord->mark >= $marksFail) {
                $stats['s_pass']++;
            } else {
                $stats['s_fail']++;
                $stats['crch_fail'] += $markRecord->subject->crch ?? 0;
            }

            // حساب CRCH والنقاط للمواد الفريدة
            if (! isset($uniqueSubjects[$markRecord->subject_code])) {
                $subject = $this->getSubjectModel($markRecord->subject_code);

                if ($subject) {
                    $stats['crch'] += $subject->crch ?? 0;
                    $stats['points'] += ($markRecord->point * ($subject->crch ?? 0));
                }
                $uniqueSubjects[$markRecord->subject_code] = true;
            }
        }

        // حساب المعدل التراكمي
        $stats['gpa'] = ($stats['crch']) > 0  // 🔴 التعديل هنا: التحقق من crch بدلاً من countSubject
            ? round($stats['points'] / ($stats['crch']), 2)
            : 0;

        return $stats;
    }

    /**
     * تحديد التوصية المناسبة للطالب
     */
    public function determineRecommendation(array $stats): string
    {
        $marks = $this->getStudentMarks(\true);

        // $failurePercentage = ($stats['crch']) > 0
        // ? (($stats['crch_fail'] / $stats['crch']) * 100 / 10) * 10
        // : 0;

        $failurePercentage = ($stats['crch']) > 0
            ? ($stats['crch_fail'] / $stats['crch']) * 100
            : 0;

        // غير مكتمل
        if ($stats['countSubject'] != $marks->count()) {
            Log::error("Incomplete data for student {$this->university_no}. Total subjects: {$stats['countSubject']}, Inserted subjects: ".$marks->count());

            return '9999';
        }

        if ($this->examType == 1) {
            return $this->handleFinalExamRecommendation($stats, $failurePercentage);
        } elseif ($this->examType == 2) {
            return $this->handleMidtermRecommendation($stats, $failurePercentage);
        }

        return '';
    }

    private function handleFinalExamRecommendation(array $stats, float $failurePercentage): string
    {
        // نجاح
        //  dd($stats ,$failurePercentage);
        if ($stats['s_fail'] == 0 && $stats['s_abs'] == 0) {
            return '1005';
        }

        //   ملحق
        if ($failurePercentage < 50) {
            return '4001';
        }

        //   اعادة فصل
        if ($failurePercentage > 50 && $failurePercentage < 75) {
            if ($this->hasPreviousRepeat()) {
                return '4004'; // فصل أكاديمي إذا رسب في سنة إعادة
            }

            return '2002';
        }

        //   فصل من الدراسة
        if ($failurePercentage >= 75) {
            return '4004';
        }

        // إذا لم يوجد رسوب أو غياب، نعتمد على المعدل (ناجح)
        return $this->getGpaBasedRecommendation($stats['gpa']);
    }

    private function handleMidtermRecommendation(array $stats, float $failurePercentage): string
    {
        // dd($stats['s_fail'],$failurePercentage, $this->hasPreviousExternal());
        // if ($failurePercentage > 50) {
        if ($failurePercentage > 70 && ! $this->hasPreviousRepeat()) {
            return '2001'; // كل المقررات
        } elseif ($failurePercentage > 50 && $failurePercentage <= 70 && ! $this->hasPreviousRepeat()) {
            return '2002'; // ببعض المقررات
        } elseif ($stats['s_fail'] == 1 && ! $this->hasPreviousExternal() && ! $this->hasPreviousRepeat()) {
            return '4005'; // ازالة رسوب
        } elseif ($stats['s_fail'] > 0 && $this->hasPreviousRepeat()) {
            return '4004'; // فصل اكاديمي
        } else {
            return $this->getGpaBasedRecommendation($stats['gpa']);
        }
    }

    private function getGpaBasedRecommendation(float $gpa): string
    {
        if ($gpa >= 80) {
            return '1001';
        }
        if ($gpa >= 70) {
            return '1002';
        }
        if ($gpa >= 60) {
            return '1003';
        }

        return '1004';
    }

    private function getPassingMark(): int
    {
        return $this->studentDetails['stady_code'] == 20060002 ? 40 : 50;
    }

    private function hasPreviousExternal(): bool
    {
        return Recom::where('university_no', $this->university_no)
            ->where('recom_code', 4005)
            ->where('batch_code', '<>', $this->batch_code)
            ->exists();
    }

    private function hasPreviousRepeat(): bool
    {
        return Recom::where('university_no', $this->university_no)
            ->where('level_code', $this->level_code)
            ->whereIn('recom_code', [2001, 2002])
            ->where('batch_code', '<>', $this->batch_code)
            ->exists();
    }

    private function getPreviousExternalExamCount(): int
    {
        return Recom::where('university_no', $this->university_no)
            // ->where('level_code', $this->level_code)
            ->whereIn('recom_code', [4005, 4004])
            ->where('batch_code', '<>', $this->batch_code)
            ->count();
    }

    private function getPreviousRepeatCount(): int
    {
        return Recom::where('university_no', $this->university_no)
            ->where('level_code', '<>', $this->level_code)
            ->whereIn('recom_code', [2001, 2002])
            ->where('batch_code', '<>', $this->batch_code)
            ->count();
    }

    private function getSubjects($Fianal = \false)
    {
        return Subject::where('faculty_code', $this->faculty_code)
            ->where('level_code', $this->level_code)
            // ->when(!$Fianal, function ($query) {
            //     $query->where('semester_id', $this->semester);
            // })
            ->where('department_code', $this->studentDetails['department_code'])
            ->when($this->studentDetails['special_code'], fn ($q) => $q->where('special_code', $this->studentDetails['special_code']))
            ->get();
    }

    private function getStudentMarks($Fianal = \false)
    {
        return Mark::where('university_no', $this->university_no)
            ->with('subject')
            ->where('faculty_code', $this->faculty_code)
            ->where('level_code', $this->level_code)
            ->where('batch_code', $this->batch_code)
            ->when(! $Fianal, function ($query) {
                $query->where('semester', $this->semester);
            })
            ->get()
            ->sortByDesc('id') // prefer highest examtype by sorting by ID descending
            ->unique('subject_code'); // keep only unique subject_code (keeping the first occurrence which will be the highest ID)
    }

    private function getSubjectModel(string $subject_code): ?Subject
    {
        return Subject::where('code', $subject_code)->first();
    }
}
