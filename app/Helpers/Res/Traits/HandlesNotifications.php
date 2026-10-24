<?php

namespace App\Helpers\Res\Traits;

use App\Models\Res\LtRecom;
use Filament\Notifications\Notification;

trait HandlesNotifications
{
    /**
     * إرسال إشعار نجاح حفظ الدرجة
     */
    protected static function sendSuccessNotification(string $university_no, string $subjectName): void
    {
        Notification::make()
            ->title('نجاح!')
            ->body('تم حفظ الدرجة بنجاح للطالب '.$university_no.' في مادة '.$subjectName.'.')
            ->success()
            ->send();
    }

    protected static function sendStatisticsSuccessNotification(string $recommendation): void
    {
        $recommendationModel = LtRecom::where('code', $recommendation)->first();
        Notification::make()
            ->title('نجاح!')
            ->body('تم تحديث إحصائيات الطالب بنجاح لـ '.($recommendationModel->name_ar ?? $recommendation).'.')
            ->success()
            ->send();
    }

    /**
     * إرسال إشعار خطأ في حفظ الدرجة
     */
    protected static function sendMarkErrorNotification(string $message): void
    {
        Notification::make()
            ->title('خطأ!')
            ->body($message)
            ->danger()
            ->send();
    }

    /**
     * إرسال إشعار خطأ في حفظ السبب
     */
    protected static function sendReasonErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Error!')
            ->body($message)
            ->danger()
            ->send();
    }

    /**
     * إرسال إشعار خطأ في تحديث الإحصائيات
     */
    protected static function sendStatisticsErrorNotification(string $message): void
    {
        Notification::make()
            ->title('خطأ!')
            ->body($message)
            ->danger()
            ->send();
    }
}
