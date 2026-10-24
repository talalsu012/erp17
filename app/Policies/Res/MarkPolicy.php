<?php

declare(strict_types=1);

namespace App\Policies\Res;

use App\Models\Res\Mark;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MarkPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Mark');
    }

    public function view(AuthUser $authUser, Mark $mark): bool
    {
        return $authUser->can('View:Mark');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Mark');
    }

    public function update(AuthUser $authUser, Mark $mark): bool
    {
        return $authUser->can('Update:Mark');
    }

    public function delete(AuthUser $authUser, Mark $mark): bool
    {
        return $authUser->can('Delete:Mark');
    }

    public function restore(AuthUser $authUser, Mark $mark): bool
    {
        return $authUser->can('Restore:Mark');
    }

    public function forceDelete(AuthUser $authUser, Mark $mark): bool
    {
        return $authUser->can('ForceDelete:Mark');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Mark');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Mark');
    }

    public function replicate(AuthUser $authUser, Mark $mark): bool
    {
        return $authUser->can('Replicate:Mark');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Mark');
    }
}
