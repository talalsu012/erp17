<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * يعيد تحميل كل جداول الإعدادات من لقطات JSON مأخوذة من قاعدة البيانات (database/seeders/data).
 */
class SettingsSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const TABLES = [
        'reg_campuses',
        'reg_faculties',
        'reg_departments',
        'reg_specials',
        'reg_levels',
        'reg_ltcolumns',
        'reg_open_reg',
        'res_grades',
        'res_resons',
        'res_ltrecoms',
        'subjects',
        'prints',
        'cert_setting',
        'cert_style',
        'fields',
        'roles',
        'permissions',
        'role_has_permissions',
        'role_field_permissions',
    ];

    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (self::TABLES as $table) {
            $rows = json_decode((string) file_get_contents(database_path("seeders/data/{$table}.json")), true, flags: JSON_THROW_ON_ERROR);

            DB::table($table)->truncate();

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
