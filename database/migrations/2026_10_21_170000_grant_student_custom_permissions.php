<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const NEW_PERMISSIONS = ['ResetAccount:Student', 'BulkUpdate:Student'];

    /**
     * كان إجراءا «حذف حساب الطالب» و«التحديث الجماعي» متاحين لكل من يرى قائمة الطلاب.
     * تُنشأ لهما صلاحيتان مستقلتان وتُمنحان لكل دور وكل مستخدم يملك ViewAny:Student
     * حتى لا يفقد أحد وصوله عند تفعيل الفحص.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $viewAny = Permission::query()->where('name', 'ViewAny:Student')->where('guard_name', 'web')->first();

        foreach (self::NEW_PERMISSIONS as $name) {
            $permission = Permission::findOrCreate($name, 'web');

            if (! $viewAny) {
                continue;
            }

            foreach (['role_has_permissions', 'model_has_permissions'] as $table) {
                $holders = DB::table($table)->where('permission_id', $viewAny->getKey())->get();

                foreach ($holders as $holder) {
                    $row = (array) $holder;
                    $row['permission_id'] = $permission->getKey();

                    DB::table($table)->insertOrIgnore($row);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        Permission::query()->whereIn('name', self::NEW_PERMISSIONS)->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
