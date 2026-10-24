<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تنظيف الجدول قبل الإدراج
        DB::table('reg_campuses')->delete();
        $campuses = [
            [
                'code' => 1001001,
                'name_ar' => 'الجامعة الرئيسية',
                'name_en' => 'Main Campus',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 2001002,
                'name_ar' => 'فرع بحري',
                'name_en' => 'Bahri Campus',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 3001003,
                'name_ar' => 'فرع شمال الخرطوم',
                'name_en' => 'North Khartoum Campus',
                'is_visible' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 4001004,
                'name_ar' => 'فرع شرق الخرطوم',
                'name_en' => 'East Khartoum Campus',
                'is_visible' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 5001005,
                'name_ar' => 'فرع غرب الخرطوم',
                'name_en' => 'West Khartoum Campus',
                'is_visible' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('reg_campuses')->insert($campuses);
        // يمكنك إضافة المزيد من البيانات هنا إذا لزم الأمر
        DB::table('reg_faculties')->delete();
        $facilities = [
            [
                'code' => 1010001,
                'campus_code' => 1001001,
                'name_ar' => 'كلية الهندسة',
                'name_en' => 'Faculty of Engineering',
                'is_visible' => true,
                'start_reg' => '2024-09-01',
                'end_reg' => '2024-09-30',
                'primary_hex' => '#FF5733',
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1020002,
                'campus_code' => 1001001,
                'name_ar' => 'كلية الطب',
                'name_en' => 'Faculty of Medicine',
                'is_visible' => true,
                'start_reg' => '2024-10-01',
                'end_reg' => '2024-10-31',
                'primary_hex' => '#33C1FF',
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 2010003,
                'campus_code' => 2001002,
                'name_ar' => 'كلية العلوم الإدارية',
                'name_en' => 'Faculty of Business Administration',
                'is_visible' => true,
                'start_reg' => '2024-11-01',
                'end_reg' => '2024-11-30',
                'primary_hex' => '#28A745',
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 2020004,
                'campus_code' => 2001002,
                'name_ar' => 'كلية العلوم الاجتماعية',
                'name_en' => 'Faculty of Social Sciences',
                'is_visible' => false,
                'start_reg' => '2024-12-01',
                'end_reg' => '2024-12-31',
                'primary_hex' => '#FFC107',
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('reg_faculties')->insert($facilities);

        // يمكنك إضافة المزيد من البيانات هنا إذا لزم الأمر
        DB::table('reg_departments')->delete();
        $departments = [
            [
                'code' => 1010001,
                'faculty_code' => 1010001,
                'name_ar' => 'قسم الهندسة الكهربائية',
                'name_en' => 'Department of Electrical Engineering',
                'is_visible' => true,
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1010002,
                'faculty_code' => 1010001,
                'name_ar' => 'قسم الهندسة الميكانيكية',
                'name_en' => 'Department of Mechanical Engineering',
                'is_visible' => true,
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1020001,
                'faculty_code' => 1020002,
                'name_ar' => 'قسم الجراحة',
                'name_en' => 'Department of Surgery',
                'is_visible' => true,
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1020002,
                'faculty_code' => 1020002,
                'name_ar' => 'قسم الطب الباطني',
                'name_en' => 'Department of Internal Medicine',
                'is_visible' => false,
                'results_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('reg_departments')->insert($departments);

        // يمكنك إضافة المزيد من البيانات هنا إذا لزم الأمر
        DB::table('reg_specials')->delete();
        $specials = [
            [
                'code' => 1010001,
                'department_code' => 1010001,
                'name_ar' => 'هندسة الاتصالات',
                'name_en' => 'Communications Engineering',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1010002,
                'department_code' => 1010001,
                'name_ar' => 'هندسة الطاقة',
                'name_en' => 'Power Engineering',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1020001,
                'department_code' => 1020001,
                'name_ar' => 'جراحة القلب',
                'name_en' => 'Cardiac Surgery',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 1020002,
                'department_code' => 1020001,
                'name_ar' => 'جراحة الأعصاب',
                'name_en' => 'Neurosurgery',
                'is_visible' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('reg_specials')->insert($specials);

        DB::table('reg_levels')->delete();
        $levels = [
            [
                'code' => 1,
                'name_ar' => 'المستوى الأول',
                'name_en' => 'Level 1',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 2,
                'name_ar' => 'المستوى الثاني',
                'name_en' => 'Level 2',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 3,
                'name_ar' => 'المستوى الثالث',
                'name_en' => 'Level 3',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 4,
                'name_ar' => 'المستوى الرابع',
                'name_en' => 'Level 4',
                'is_visible' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('reg_levels')->insert($levels);

    }
}
