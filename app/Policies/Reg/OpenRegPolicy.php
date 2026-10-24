<?php

declare(strict_types=1);

namespace App\Policies\Reg;

use App\Models\Reg\OpenReg;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class OpenRegPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OpenReg');
    }

    public function view(AuthUser $authUser, OpenReg $openReg): bool
    {
        return $authUser->can('View:OpenReg');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OpenReg');
    }

    public function update(AuthUser $authUser, OpenReg $openReg): bool
    {
        return $authUser->can('Update:OpenReg');
    }

    public function delete(AuthUser $authUser, OpenReg $openReg): bool
    {
        return $authUser->can('Delete:OpenReg');
    }

    public function restore(AuthUser $authUser, OpenReg $openReg): bool
    {
        return $authUser->can('Restore:OpenReg');
    }

    public function forceDelete(AuthUser $authUser, OpenReg $openReg): bool
    {
        return $authUser->can('ForceDelete:OpenReg');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OpenReg');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OpenReg');
    }

    public function replicate(AuthUser $authUser, OpenReg $openReg): bool
    {
        return $authUser->can('Replicate:OpenReg');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OpenReg');
    }
}
