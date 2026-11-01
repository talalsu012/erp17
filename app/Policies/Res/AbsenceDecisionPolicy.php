<?php

declare(strict_types=1);

namespace App\Policies\Res;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Res\AbsenceDecision;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsenceDecisionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AbsenceDecision');
    }

    public function view(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('View:AbsenceDecision');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AbsenceDecision');
    }

    public function update(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('Update:AbsenceDecision');
    }

    public function delete(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('Delete:AbsenceDecision');
    }

    public function restore(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('Restore:AbsenceDecision');
    }

    public function forceDelete(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('ForceDelete:AbsenceDecision');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AbsenceDecision');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AbsenceDecision');
    }

    public function replicate(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('Replicate:AbsenceDecision');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AbsenceDecision');
    }

}