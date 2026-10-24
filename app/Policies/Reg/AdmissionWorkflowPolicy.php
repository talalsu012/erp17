<?php

declare(strict_types=1);

namespace App\Policies\Reg;

use App\Models\Reg\AdmissionWorkflow;
use App\Services\AdmissionWorkflowService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * الاطلاع للأدوار المشاركة في المخطط؛ والتعديل عبر إجراءات الخدمة فقط (لا نموذج تعديل حر).
 */
class AdmissionWorkflowPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole(AdmissionWorkflowService::participatingRoles());
    }

    public function view(AuthUser $authUser, AdmissionWorkflow $workflow): bool
    {
        return $this->viewAny($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasAnyRole([AdmissionWorkflowService::ROLE_REGISTRAR, AdmissionWorkflowService::ROLE_SUPER_ADMIN]);
    }

    public function update(AuthUser $authUser, AdmissionWorkflow $workflow): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AdmissionWorkflow $workflow): bool
    {
        return false;
    }
}
