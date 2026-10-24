<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StudentAccount;
use Illuminate\Foundation\Auth\User as AuthUser;

class StudentAccountPolicy
{
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentAccount');
    }

    public function view(AuthUser $authUser, StudentAccount $studentAccount): bool
    {
        return $authUser->can('View:StudentAccount');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, StudentAccount $studentAccount): bool
    {
        return $authUser->can('Update:StudentAccount');
    }

    public function delete(AuthUser $authUser, StudentAccount $studentAccount): bool
    {
        return $authUser->can('Delete:StudentAccount');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function sendResetLink(AuthUser $authUser, StudentAccount $studentAccount): bool
    {
        return $authUser->can('SendResetLink:StudentAccount');
    }
}
