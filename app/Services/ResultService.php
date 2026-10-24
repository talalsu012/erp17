<?php

namespace App\Services;

use App\Models\Res\SubjectApprovalStatus;
use App\Reg\MarkApprovalStage;
use Filament\Notifications\Notification;

class ResultService
{
    /**
     * Get the current academic year.
     */
    public function getCurrentAcademicYear(): string
    {
        return date('Y').'-'.(date('Y') + 1);
    }

    /**
     * Get the current semester.
     */
    public function getCurrentSemester(): string
    {
        return date('n') <= 6 ? '1st Semester' : '2nd Semester';
    }

    public function getCurrentStage($subject, $batch): string
    {
        $currentStage = SubjectApprovalStatus::where('subject_code', $subject)
            ->where('batch_code', $batch)
            ->value('current_stage');

        if ($currentStage instanceof \BackedEnum) {
            return $currentStage->value;
        }

        return $currentStage ?? MarkApprovalStage::Professor->value;
    }

    public function advanceStage($subject, $batch): void
    {

        if (! $subject || ! $batch) {
            Notification::make()
                ->title('خطأ')
                ->body('يرجى اختيار المادة والدفعة أولاً')
                ->danger()
                ->send();
        }
        $currentStage = $this->getCurrentStage($subject, $batch);

        $newStage = match ($currentStage) {
            MarkApprovalStage::Professor->value => MarkApprovalStage::HeadDepartment->value,
            MarkApprovalStage::HeadDepartment->value => MarkApprovalStage::Dean->value,
            MarkApprovalStage::Dean->value => MarkApprovalStage::ScientificAffairs->value,
            MarkApprovalStage::ScientificAffairs->value => MarkApprovalStage::Approved->value,
            default => null, // إذا كانت المرحلة غير معروفة أو وصلت للمعتمد النهائي، لا نفعل شيء أو نعيد رسالة
        };

        if ($newStage) {
            SubjectApprovalStatus::updateOrCreate(
                [
                    'subject_code' => $subject,
                    'batch_code' => $batch,
                ],
                [
                    'current_stage' => $newStage,
                ]
            );
        }
    }
}
