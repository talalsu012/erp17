<?php

declare(strict_types=1);

namespace App\Policies\Reg;

use App\Models\Reg\Special;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SpecialPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Special');
    }

    public function view(AuthUser $authUser, Special $special): bool
    {
        return $authUser->can('View:Special');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Special');
    }

    public function update(AuthUser $authUser, Special $special): bool
    {
        return $authUser->can('Update:Special');
    }

    public function delete(AuthUser $authUser, Special $special): bool
    {
        return $authUser->can('Delete:Special');
    }

    public function restore(AuthUser $authUser, Special $special): bool
    {
        return $authUser->can('Restore:Special');
    }

    public function forceDelete(AuthUser $authUser, Special $special): bool
    {
        return $authUser->can('ForceDelete:Special');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Special');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Special');
    }

    public function replicate(AuthUser $authUser, Special $special): bool
    {
        return $authUser->can('Replicate:Special');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Special');
    }
}
