<?php

declare(strict_types=1);

namespace App\Policies\Cert;

use App\Models\Cert\CertStyle;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CertStylePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CertStyle');
    }

    public function view(AuthUser $authUser, CertStyle $certStyle): bool
    {
        return $authUser->can('View:CertStyle');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CertStyle');
    }

    public function update(AuthUser $authUser, CertStyle $certStyle): bool
    {
        return $authUser->can('Update:CertStyle');
    }

    public function delete(AuthUser $authUser, CertStyle $certStyle): bool
    {
        return $authUser->can('Delete:CertStyle');
    }

    public function restore(AuthUser $authUser, CertStyle $certStyle): bool
    {
        return $authUser->can('Restore:CertStyle');
    }

    public function forceDelete(AuthUser $authUser, CertStyle $certStyle): bool
    {
        return $authUser->can('ForceDelete:CertStyle');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CertStyle');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CertStyle');
    }

    public function replicate(AuthUser $authUser, CertStyle $certStyle): bool
    {
        return $authUser->can('Replicate:CertStyle');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CertStyle');
    }
}
