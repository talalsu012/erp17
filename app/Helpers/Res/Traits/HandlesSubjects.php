<?php

namespace App\Helpers\Res\Traits;

use App\Models\Res\Reson;
use App\Models\Res\Subject;

trait HandlesSubjects
{
    /**
     * التحقق من صحة بيانات الدرجة المدخلة
     * CREATE TABLE `subjects` (
     */
    protected static function validateMarkInput(
        string $EntryName,
        mixed $value,
        int $maxExam = 100
    ): bool {

        // 1. التحقق من أن القيمة رقمية
        if (! is_numeric($value)) {
            self::sendMarkErrorNotification('الدرجة المدخلة غير صحيحة. يجب أن تكون قيمة رقمية.');

            return false;
        }
        $value = (float) $value;

        // 3. منطق التحقق بناءً على نوع المادة (الامتحان من 100 أم لا)
        $maxCwork = 100 - $maxExam;       // الباقي لأعمال السنة (مثلاً 30 أو 0)

        if ($EntryName === 'cwork') {
            // إذا كان الامتحان يمثل 100%، فلا يسمح بأعمال السنة
            if ($maxExam >= 100) {
                self::sendMarkErrorNotification('هذه المادة ليس لها أعمال سنة (نسبة الامتحان 100%).');

                return false;
            }

            // التحقق من تجاوز الحد الأقصى لأعمال السنة
            if ($value > $maxCwork) {
                self::sendMarkErrorNotification("درجة أعمال السنة تتجاوز الحد الأقصى المسموح ({$maxCwork}).");

                return false;
            }
        }

        if ($EntryName === 'mark') {
            // التحقق من تجاوز الحد الأقصى للامتحان
            if ($value > $maxExam) {
                self::sendMarkErrorNotification("درجة الامتحان تتجاوز الحد الأقصى المسموح ({$maxExam})).");

                return false;
            }
        }

        // التحقق من الدرجات السالبة
        if ($value < 0) {
            self::sendMarkErrorNotification('لا يمكن إدخال درجة أقل من الصفر.');

            return false;
        }

        return true;
    }

    /**
     * التحقق من صحة بيانات السبب المدخل
     */
    protected static function validateReasonInput(
        string $subject_code,
        string $reson
    ): bool {
        if (empty($subject_code) || empty($reson) || ! is_numeric($reson)) {
            self::sendReasonErrorNotification('Invalid subject or reason');

            return false;
        }

        return true;
    }

    /**
     * جلب تفاصيل المادة
     */
    protected static function getSubjectDetails(string $subject_code): array
    {
        $subject = Subject::where('code', $subject_code)->firstOrFail();

        return [
            'code' => $subject->code,
            'name_ar' => $subject->name_ar,
            'faculty_code' => $subject->faculty_code,
            'department_code' => $subject->department_code,
            'special_code' => $subject->special_code,
            'level_code' => $subject->level_code,
            'semester_id' => $subject->semester_id,
            'exam' => $subject->exam,
            'plan' => $subject->plan,
            'status' => $subject->status,
            // يمكن إضافة المزيد من الحقول حسب الحاجة
        ];
    }

    /**
     * إعداد معايير البحث عن الدرجة
     */
    protected static function prepareMarkCriteria(
        string $university_no,
        array $subjectDetails,
        string $batch_code,
        string $examType
    ): array {
        return [
            'university_no' => $university_no,
            'subject_code' => $subjectDetails['code'],
            'faculty_code' => $subjectDetails['faculty_code'],
            'department_code' => $subjectDetails['department_code'],
            'special_code' => $subjectDetails['special_code'],
            'level_code' => $subjectDetails['level_code'],
            'semester' => $subjectDetails['semester_id'],
            'batch_code' => $batch_code,
            'examType' => $examType,
        ];
    }

    /**
     * إعداد بيانات الدرجة للحفظ
     */
    protected static function prepareMarkData(string $type, float $mark, string $grade, float $point): array
    {

        return [
            $type => (float) $mark,
            'grade' => $grade,
            'reson_code' => null,
            'point' => $point,
        ];
    }

    /**
     * إعداد بيانات السبب للحفظ
     */
    protected static function prepareReasonData(string $reson): array
    {
        $data = ['reson_code' => $reson];

        if ($reson > 2000) {
            $resonDesc = Reson::where('code', $reson)->first();
            if ($resonDesc) {
                $data = [
                    'mark' => null,
                    'cwork' => null,
                    'grade' => $resonDesc->description,
                    'reson_code' => $reson,
                ];
            }
        }

        return $data;
    }
}
