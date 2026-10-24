<?php

namespace App\Policies;

use App\Models\Res\Mark;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarkPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['professor', 'head_department', 'dean', 'scientific_affairs', 'admin']);
    }

    public function view(User $user, Mark $Mark): bool
    {
        return $this->canAccessStage($user, $Mark);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('professor') || $user->hasRole('admin');
    }

    public function update(User $user, Mark $Mark): bool
    {
        return $this->canAccessStage($user, $Mark);
    }

    public function delete(User $user, Mark $Mark): bool
    {
        return $user->hasRole('admin'); // فقط الإداري يحذف
    }

    private function canAccessStage(User $user, Mark $Mark): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return match ($Mark->approval_stage) {
            MarkApprovalStage::Professor => $user->hasRole('professor') && $this->isSubjectOwner($user, $Mark),

            MarkApprovalStage::HeadDepartment => $user->hasRole('head_department'),

            MarkApprovalStage::Dean => $user->hasRole('dean'),

            MarkApprovalStage::ScientificAffairs => $user->hasRole('scientific_affairs'),

            MarkApprovalStage::Approved,
            MarkApprovalStage::Rejected => false, // بعد الإغلاق لا يُعدل

            default => false,
        };
    }

    private function isSubjectOwner(User $user, Mark $Mark): bool
    {
        if (! $user->staff) {
            return false;
        }

        return $user->staff->subjects()
            ->where('subject_code', $Mark->subject_code)
            ->where('batch_code', $Mark->batch_code)
            ->exists();
    }
}
