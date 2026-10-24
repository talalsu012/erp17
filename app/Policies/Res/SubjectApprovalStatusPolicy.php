<?php

declare(strict_types=1);

namespace App\Policies\Res;

use App\Models\Res\SubjectApprovalStatus;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SubjectApprovalStatusPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubjectApprovalStatus');
    }

    public function view(AuthUser $authUser, SubjectApprovalStatus $subjectApprovalStatus): bool
    {
        return $authUser->can('View:SubjectApprovalStatus');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubjectApprovalStatus');
    }

    public function update(AuthUser $authUser, SubjectApprovalStatus $subjectApprovalStatus): bool
    {
        return $authUser->can('Update:SubjectApprovalStatus');
    }

    public function delete(AuthUser $authUser, SubjectApprovalStatus $subjectApprovalStatus): bool
    {
        return $authUser->can('Delete:SubjectApprovalStatus');
    }

    public function restore(AuthUser $authUser, SubjectApprovalStatus $subjectApprovalStatus): bool
    {
        return $authUser->can('Restore:SubjectApprovalStatus');
    }

    public function forceDelete(AuthUser $authUser, SubjectApprovalStatus $subjectApprovalStatus): bool
    {
        return $authUser->can('ForceDelete:SubjectApprovalStatus');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubjectApprovalStatus');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubjectApprovalStatus');
    }

    public function replicate(AuthUser $authUser, SubjectApprovalStatus $subjectApprovalStatus): bool
    {
        return $authUser->can('Replicate:SubjectApprovalStatus');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubjectApprovalStatus');
    }
}
