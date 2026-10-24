<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const NEW_PERMISSIONS = [
        'ViewAny:StudentAccount',
        'View:StudentAccount',
        'Update:StudentAccount',
        'Delete:StudentAccount',
        'SendResetLink:StudentAccount',
    ];

    /**
     * كان حذف حساب الطالب متاحاً لمن يملك ResetAccount:Student؛ تُمنح صلاحيات مورد حسابات البوابة
     * الجديد لكل دور ومستخدم يملكها حتى لا يفقد أحد قدرته الحالية.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $source = Permission::query()->where('name', 'ResetAccount:Student')->where('guard_name', 'web')->first();

        foreach (self::NEW_PERMISSIONS as $name) {
            $permission = Permission::findOrCreate($name, 'web');

            if (! $source) {
                continue;
            }

            foreach (['role_has_permissions', 'model_has_permissions'] as $table) {
                foreach (DB::table($table)->where('permission_id', $source->getKey())->get() as $holder) {
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
