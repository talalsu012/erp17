<?php

declare(strict_types=1);

namespace App\Policies\attendances;

use App\Models\attendances\Lecture;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LecturePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Lecture');
    }

    public function view(AuthUser $authUser, Lecture $lecture): bool
    {
        return $authUser->can('View:Lecture');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Lecture');
    }

    public function update(AuthUser $authUser, Lecture $lecture): bool
    {
        return $authUser->can('Update:Lecture');
    }

    public function delete(AuthUser $authUser, Lecture $lecture): bool
    {
        return $authUser->can('Delete:Lecture');
    }

    public function restore(AuthUser $authUser, Lecture $lecture): bool
    {
        return $authUser->can('Restore:Lecture');
    }

    public function forceDelete(AuthUser $authUser, Lecture $lecture): bool
    {
        return $authUser->can('ForceDelete:Lecture');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Lecture');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Lecture');
    }

    public function replicate(AuthUser $authUser, Lecture $lecture): bool
    {
        return $authUser->can('Replicate:Lecture');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Lecture');
    }
}
