<?php

namespace App\Helpers\Res\Traits;

use Illuminate\Support\Facades\Log;

trait HandlesRecommendations
{
    /**
     * معالجة أخطاء حفظ الدرجة
     */
    protected static function handleMarkError(
        \Exception $e,
        string $university_no,
        string $subject_code,
        float $mark,
        string $examType,
        string $batch_code
    ): void {
        self::sendMarkErrorNotification('فشل في حفظ البيانات: '.$e->getMessage());
        Log::error('MarkHelper: Failed to save mark. Exception: '.$e->getMessage().
            ' Data: '.json_encode(compact('university_no', 'subject_code', 'mark', 'examType', 'batch_code')));
    }

    /**
     * معالجة أخطاء حفظ السبب
     */
    protected static function handleReasonError(\Throwable $e): void
    {
        self::sendReasonErrorNotification('Failed to save data: '.$e->getMessage());
    }

    /**
     * معالجة أخطاء تحديث الإحصائيات
     */
    protected static function handleStatisticsError(
        \Exception $e,
        string $university_no,
        string $faculty_code,
        string $level_code,
        string $batch_code,
        string $semester,
        string $examType
    ): void {
        self::sendStatisticsErrorNotification('فشل في تحديث إحصائيات الطالب: '.$e->getMessage());
        Log::error('MarkHelper: Failed to update student recommendations. Exception: '.
            $e->getMessage().' Data: '.json_encode(compact(
                'university_no', 'faculty_code', 'level_code', 'batch_code', 'semester', 'examType'
            )));
    }
}
