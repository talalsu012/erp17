<?php

declare(strict_types=1);

namespace App\Policies\Reg;

use App\Models\Reg\MedicalFitnessDecisions;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MedicalFitnessDecisionsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MedicalFitnessDecisions');
    }

    public function view(AuthUser $authUser, MedicalFitnessDecisions $medicalFitnessDecisions): bool
    {
        return $authUser->can('View:MedicalFitnessDecisions');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MedicalFitnessDecisions');
    }

    public function update(AuthUser $authUser, MedicalFitnessDecisions $medicalFitnessDecisions): bool
    {
        return $authUser->can('Update:MedicalFitnessDecisions');
    }

    public function delete(AuthUser $authUser, MedicalFitnessDecisions $medicalFitnessDecisions): bool
    {
        return $authUser->can('Delete:MedicalFitnessDecisions');
    }

    public function restore(AuthUser $authUser, MedicalFitnessDecisions $medicalFitnessDecisions): bool
    {
        return $authUser->can('Restore:MedicalFitnessDecisions');
    }

    public function forceDelete(AuthUser $authUser, MedicalFitnessDecisions $medicalFitnessDecisions): bool
    {
        return $authUser->can('ForceDelete:MedicalFitnessDecisions');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MedicalFitnessDecisions');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MedicalFitnessDecisions');
    }

    public function replicate(AuthUser $authUser, MedicalFitnessDecisions $medicalFitnessDecisions): bool
    {
        return $authUser->can('Replicate:MedicalFitnessDecisions');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MedicalFitnessDecisions');
    }
}
