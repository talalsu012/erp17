<?php

declare(strict_types=1);

namespace App\Policies\Reg;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Reg\AdmissionWorkflow;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdmissionWorkflowPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdmissionWorkflow');
    }

    public function view(AuthUser $authUser, AdmissionWorkflow $admissionWorkflow): bool
    {
        return $authUser->can('View:AdmissionWorkflow');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdmissionWorkflow');
    }

    public function update(AuthUser $authUser, AdmissionWorkflow $admissionWorkflow): bool
    {
        return $authUser->can('Update:AdmissionWorkflow');
    }

    public function delete(AuthUser $authUser, AdmissionWorkflow $admissionWorkflow): bool
    {
        return $authUser->can('Delete:AdmissionWorkflow');
    }

    public function restore(AuthUser $authUser, AdmissionWorkflow $admissionWorkflow): bool
    {
        return $authUser->can('Restore:AdmissionWorkflow');
    }

    public function forceDelete(AuthUser $authUser, AdmissionWorkflow $admissionWorkflow): bool
    {
        return $authUser->can('ForceDelete:AdmissionWorkflow');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdmissionWorkflow');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdmissionWorkflow');
    }

    public function replicate(AuthUser $authUser, AdmissionWorkflow $admissionWorkflow): bool
    {
        return $authUser->can('Replicate:AdmissionWorkflow');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdmissionWorkflow');
    }

}