<?php

namespace App\Services\Res;

use App\Models\Res\SubjectApprovalStatus;
use App\Reg\MarkApprovalStage;

class ResultService
{
    /**
     * إرجاع المرحلة الحالية للمادة في الدفعة المحددة
     */
    public function getCurrentStage($subjectCode, $batchCode): MarkApprovalStage
    {
        if (empty($subjectCode) || empty($batchCode)) {
            return MarkApprovalStage::Professor; // المرحلة الافتراضية
        }

        $approval = SubjectApprovalStatus::where('subject_code', $subjectCode)
            ->where('batch_code', $batchCode)
            ->first();

        return $approval?->current_stage ?? MarkApprovalStage::Professor;
    }
}
