<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentAccountPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentAccount');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:StudentAccount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudentAccount');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:StudentAccount');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:StudentAccount');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:StudentAccount');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:StudentAccount');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudentAccount');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudentAccount');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:StudentAccount');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudentAccount');
    }

}