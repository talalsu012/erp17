<?php

declare(strict_types=1);

namespace App\Policies\Cert;

use App\Models\Cert\CertStudent;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CertStudentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CertStudent');
    }

    public function view(AuthUser $authUser, CertStudent $certStudent): bool
    {
        return $authUser->can('View:CertStudent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CertStudent');
    }

    public function update(AuthUser $authUser, CertStudent $certStudent): bool
    {
        return $authUser->can('Update:CertStudent');
    }

    public function delete(AuthUser $authUser, CertStudent $certStudent): bool
    {
        return $authUser->can('Delete:CertStudent');
    }

    public function restore(AuthUser $authUser, CertStudent $certStudent): bool
    {
        return $authUser->can('Restore:CertStudent');
    }

    public function forceDelete(AuthUser $authUser, CertStudent $certStudent): bool
    {
        return $authUser->can('ForceDelete:CertStudent');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CertStudent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CertStudent');
    }

    public function replicate(AuthUser $authUser, CertStudent $certStudent): bool
    {
        return $authUser->can('Replicate:CertStudent');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CertStudent');
    }
}
