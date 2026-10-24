<?php

declare(strict_types=1);

namespace App\Policies\Res;

use App\Models\Res\Recom;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RecomPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Recom');
    }

    public function view(AuthUser $authUser, Recom $recom): bool
    {
        return $authUser->can('View:Recom');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Recom');
    }

    public function update(AuthUser $authUser, Recom $recom): bool
    {
        return $authUser->can('Update:Recom');
    }

    public function delete(AuthUser $authUser, Recom $recom): bool
    {
        return $authUser->can('Delete:Recom');
    }

    public function restore(AuthUser $authUser, Recom $recom): bool
    {
        return $authUser->can('Restore:Recom');
    }

    public function forceDelete(AuthUser $authUser, Recom $recom): bool
    {
        return $authUser->can('ForceDelete:Recom');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Recom');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Recom');
    }

    public function replicate(AuthUser $authUser, Recom $recom): bool
    {
        return $authUser->can('Replicate:Recom');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Recom');
    }
}
