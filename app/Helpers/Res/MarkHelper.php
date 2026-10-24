<?php

namespace App\Helpers\Res;

use App\Helpers\Res\Services\MarkCalculationService;
use App\Helpers\Res\Traits\HandlesNotifications;
use App\Helpers\Res\Traits\HandlesRecommendations;
use App\Helpers\Res\Traits\HandlesSubjects;
use App\Helpers\StudentHelper;
use App\Models\Res\Mark;
use App\Models\Res\Recom;

class MarkHelper
{
    use HandlesNotifications, HandlesRecommendations, HandlesSubjects;

    /**
     * إدخال أو تحديث درجة الطالب
     */
    public static function InsertMark(
        string $university_no,
        string $subject_code,
        string $examType,
        string $batch_code,
        float $mark = 0,
        float $cwork = 0,
        string $EntryName = 'mark'
    ): void {
        try {
            // جلب تفاصيل المادة والطالب
            $subjectDetails = self::getSubjectDetails($subject_code);
            $studentDetails = StudentHelper::getStudentDetails($university_no);

            $maxExam = $subjectDetails['exam'] ?? 100;
            // when maxExam is 100 Set cwork to 0
            if ($maxExam == 100) {
                $cwork = 0;
            }

            // التحقق من صحة البيانات المدخلة
            $valuEntry = ($EntryName == 'mark') ? $mark : $cwork;
            if (! self::validateMarkInput($EntryName, $valuEntry, $maxExam)) {
                return;
            }

            $grade = GradeHelper::getGradeTitle(($mark + $cwork), $studentDetails['stady_code'] ?? '20060001');

            // A

            $point = GradeHelper::getGradePoint(($mark + $cwork), $studentDetails['stady_code'] ?? '20060001');
            // حفظ أو تحديث الدرجة

            $resMarkSuccess = Mark::updateOrCreate(
                self::prepareMarkCriteria($university_no, $subjectDetails, $batch_code, $examType),
                self::prepareMarkData($EntryName, $valuEntry, $grade->title, $point)
            );

            if ($resMarkSuccess) {
                self::updateStudentStatistics(
                    $university_no,
                    $subjectDetails['faculty_code'],
                    $subjectDetails['level_code'],
                    $batch_code,
                    $subjectDetails['semester_id'],
                    $examType
                );

                self::sendSuccessNotification($university_no, $subjectDetails['name_ar']);
            }
        } catch (\Exception $e) {
            self::handleMarkError($e, $university_no, $subject_code, $mark, $examType, $batch_code);
        }
    }

    /**
     * إدخال سبب لعدم وجود درجة
     */
    public static function InsertReson(
        string $university_no,
        string $subject_code,
        string $reson,
        string $examType,
        string $batch_code
    ): void {
        try {
            // التحقق من صحة البيانات المدخلة
            if (! self::validateReasonInput($subject_code, $reson)) {
                return;
            }

            // جلب تفاصيل المادة
            $subjectDetails = self::getSubjectDetails($subject_code);

            // حفظ أو تحديث السبب
            $resResonSuccess = Mark::updateOrCreate(
                self::prepareMarkCriteria($university_no, $subjectDetails, $batch_code, $examType),
                self::prepareReasonData($reson)
            );

            if ($resResonSuccess) {
                self::updateStudentStatistics(
                    $university_no,
                    $subjectDetails['faculty_code'],
                    $subjectDetails['level_code'],
                    $batch_code,
                    $subjectDetails['semester_id'],
                    $examType
                );

                self::sendSuccessNotification($university_no, $subjectDetails['name_ar']);
            }
        } catch (\Exception $e) {
            self::handleReasonError($e);
        }
    }

    /**
     * تحديث إحصائيات الطالب
     */
    public static function updateStudentStatistics(
        string $university_no,
        string $faculty_code,
        string $level_code,
        string $batch_code,
        string $semester,
        string $examType,
        bool $showNotification = true
    ): void {
        try {
            $calculationService = new MarkCalculationService(
                $university_no,
                $faculty_code,
                $level_code,
                $batch_code,
                $semester,
                $examType
            );

            // حساب الإحصائيات
            // $Final = ($semester == 2 || $examType == 2 || $faculty_code > 85) ? true : false;
            $stats = $calculationService->calculateStatistics(false);
            $recommendation = $calculationService->determineRecommendation($stats) ?: '9999';

            // إذا كان الطالب ملحق المعدل صفر
            if ($recommendation == '4001') {
                $stats['gpa'] = 0;
            }

            $studentDetails = StudentHelper::getStudentDetails($university_no);

            // حفظ أو تحديث التوصية
            Recom::updateOrCreate(
                [
                    'university_no' => $university_no,
                    'faculty_code' => $faculty_code,
                    'department_code' => $studentDetails['department_code'],
                    'level_code' => $level_code,
                    'batch_code' => $batch_code,
                    'semester' => $semester,
                    'examType' => $examType,
                ],
                // remove crch_fail from stats
                //  array_merge(array_diff_key($stats, ['crch_fail' => null]), [
                array_merge($stats, [
                    'special_code' => $studentDetails['special_code'],
                    'recom_code' => $recommendation,
                ])
            );

            if ($showNotification) {
                self::sendStatisticsSuccessNotification($recommendation);
            }
        } catch (\Throwable $e) {

            self::handleStatisticsError(
                $e,
                $university_no,
                $faculty_code,
                $level_code,
                $batch_code,
                $semester,
                $examType
            );

            throw $e;
        }
    }

    protected static function getTotalMark(string $MarkId): array
    {
        $mark = Mark::findOrFail($MarkId);

        $examMark = $mark->mark ?? 0;
        $courseWork = $mark->cwork ?? 0;

        return [
            'id' => $mark->id,
            'mark' => $examMark,
            'cwork' => $courseWork,
            'total' => $examMark + $courseWork,
        ];
    }
}
