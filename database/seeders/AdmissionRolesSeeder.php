<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Services\AdmissionWorkflowService;
use Illuminate\Database\Seeder;

/**
 * يضيف دوري الطبيب ولجنة الكلية لمخطط قبول المعاينة دون المساس بالأدوار القائمة
 * (Registrar وdean موجودان مسبقاً). آمن للتكرار.
 */
class AdmissionRolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([AdmissionWorkflowService::ROLE_DOCTOR, AdmissionWorkflowService::ROLE_COMMITTEE] as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
