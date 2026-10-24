<?php

namespace App\Services\Res;

use App\Reg\MarkApprovalStage;
use Illuminate\Database\Eloquent\Builder;

class MarkEntryAuthorization
{
    public function applyStagePermissionFilter(Builder $query, $subjectCode, $batchCode): void
    {
        if (empty($subjectCode) || empty($batchCode)) {
            return;
        }

        $currentStage = $this->getCurrentStage($subjectCode, $batchCode);
        $user = auth()->user();

        // إذا لم يكن لدى المستخدم الدور المطلوب في هذه المرحلة
        if (! $user->hasRole($currentStage->value)) {
            // يسمح فقط بعرض الطلاب الذين لديهم درجات مسجلة مسبقاً

            $query->whereHas('marks', function ($mq) use ($subjectCode, $batchCode) {
                $mq->where('subject_code', $subjectCode)
                    ->where('batch_code', $batchCode);
            });
        }
    }

    public function getCurrentStage($subjectCode, $batchCode): MarkApprovalStage
    {
        // استخدم الخدمة أو الـ Model الذي أنشأناه سابقاً (SubjectApprovalStatus)
        return app(ResultService::class)->getCurrentStage($subjectCode, $batchCode);
    }

    public function canUserEditInCurrentStage($subjectCode, $batchCode): bool
    {
        $stage = $this->getCurrentStage($subjectCode, $batchCode);

        return auth()->user()->hasRole($stage->value);
    }
}
