<?php

declare(strict_types=1);

namespace App\Policies\Res;

use App\Models\Res\LtRecom;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LtRecomPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LtRecom');
    }

    public function view(AuthUser $authUser, LtRecom $ltRecom): bool
    {
        return $authUser->can('View:LtRecom');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LtRecom');
    }

    public function update(AuthUser $authUser, LtRecom $ltRecom): bool
    {
        return $authUser->can('Update:LtRecom');
    }

    public function delete(AuthUser $authUser, LtRecom $ltRecom): bool
    {
        return $authUser->can('Delete:LtRecom');
    }

    public function restore(AuthUser $authUser, LtRecom $ltRecom): bool
    {
        return $authUser->can('Restore:LtRecom');
    }

    public function forceDelete(AuthUser $authUser, LtRecom $ltRecom): bool
    {
        return $authUser->can('ForceDelete:LtRecom');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LtRecom');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LtRecom');
    }

    public function replicate(AuthUser $authUser, LtRecom $ltRecom): bool
    {
        return $authUser->can('Replicate:LtRecom');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LtRecom');
    }
}
