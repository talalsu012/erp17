<?php

declare(strict_types=1);

namespace App\Policies\Cert;

use App\Models\Cert\CertSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CertSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CertSetting');
    }

    public function view(AuthUser $authUser, CertSetting $certSetting): bool
    {
        return $authUser->can('View:CertSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CertSetting');
    }

    public function update(AuthUser $authUser, CertSetting $certSetting): bool
    {
        return $authUser->can('Update:CertSetting');
    }

    public function delete(AuthUser $authUser, CertSetting $certSetting): bool
    {
        return $authUser->can('Delete:CertSetting');
    }

    public function restore(AuthUser $authUser, CertSetting $certSetting): bool
    {
        return $authUser->can('Restore:CertSetting');
    }

    public function forceDelete(AuthUser $authUser, CertSetting $certSetting): bool
    {
        return $authUser->can('ForceDelete:CertSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CertSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CertSetting');
    }

    public function replicate(AuthUser $authUser, CertSetting $certSetting): bool
    {
        return $authUser->can('Replicate:CertSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CertSetting');
    }
}
