<?php

declare(strict_types=1);

namespace App\Policies\attendances;

use App\Models\attendances\Grouping;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class GroupingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Grouping');
    }

    public function view(AuthUser $authUser, Grouping $grouping): bool
    {
        return $authUser->can('View:Grouping');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Grouping');
    }

    public function update(AuthUser $authUser, Grouping $grouping): bool
    {
        return $authUser->can('Update:Grouping');
    }

    public function delete(AuthUser $authUser, Grouping $grouping): bool
    {
        return $authUser->can('Delete:Grouping');
    }

    public function restore(AuthUser $authUser, Grouping $grouping): bool
    {
        return $authUser->can('Restore:Grouping');
    }

    public function forceDelete(AuthUser $authUser, Grouping $grouping): bool
    {
        return $authUser->can('ForceDelete:Grouping');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Grouping');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Grouping');
    }

    public function replicate(AuthUser $authUser, Grouping $grouping): bool
    {
        return $authUser->can('Replicate:Grouping');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Grouping');
    }
}
