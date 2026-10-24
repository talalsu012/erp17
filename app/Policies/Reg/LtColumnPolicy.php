<?php

declare(strict_types=1);

namespace App\Policies\Reg;

use App\Models\Reg\LtColumn;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LtColumnPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LtColumn');
    }

    public function view(AuthUser $authUser, LtColumn $ltColumn): bool
    {
        return $authUser->can('View:LtColumn');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LtColumn');
    }

    public function update(AuthUser $authUser, LtColumn $ltColumn): bool
    {
        return $authUser->can('Update:LtColumn');
    }

    public function delete(AuthUser $authUser, LtColumn $ltColumn): bool
    {
        return $authUser->can('Delete:LtColumn');
    }

    public function restore(AuthUser $authUser, LtColumn $ltColumn): bool
    {
        return $authUser->can('Restore:LtColumn');
    }

    public function forceDelete(AuthUser $authUser, LtColumn $ltColumn): bool
    {
        return $authUser->can('ForceDelete:LtColumn');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LtColumn');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LtColumn');
    }

    public function replicate(AuthUser $authUser, LtColumn $ltColumn): bool
    {
        return $authUser->can('Replicate:LtColumn');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LtColumn');
    }
}
