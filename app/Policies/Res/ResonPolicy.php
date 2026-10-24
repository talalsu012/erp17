<?php

declare(strict_types=1);

namespace App\Policies\Res;

use App\Models\Res\Reson;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ResonPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reson');
    }

    public function view(AuthUser $authUser, Reson $reson): bool
    {
        return $authUser->can('View:Reson');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reson');
    }

    public function update(AuthUser $authUser, Reson $reson): bool
    {
        return $authUser->can('Update:Reson');
    }

    public function delete(AuthUser $authUser, Reson $reson): bool
    {
        return $authUser->can('Delete:Reson');
    }

    public function restore(AuthUser $authUser, Reson $reson): bool
    {
        return $authUser->can('Restore:Reson');
    }

    public function forceDelete(AuthUser $authUser, Reson $reson): bool
    {
        return $authUser->can('ForceDelete:Reson');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reson');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reson');
    }

    public function replicate(AuthUser $authUser, Reson $reson): bool
    {
        return $authUser->can('Replicate:Reson');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reson');
    }
}
