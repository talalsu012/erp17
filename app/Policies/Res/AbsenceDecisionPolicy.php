<?php

declare(strict_types=1);

namespace App\Policies\Res;

use App\Models\Res\AbsenceDecision;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

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

    /** اعتماد الحرمان أو رفعه (قرار مجلس الكلية) */
    public function update(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return $authUser->can('Update:AbsenceDecision');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AbsenceDecision $absenceDecision): bool
    {
        return false;
    }
}
