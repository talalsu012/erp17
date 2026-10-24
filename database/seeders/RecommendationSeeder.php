<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecommendationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('res_ltrecoms')->insert([

            ['code' => '1001', 'name_ar' => 'ممتاز', 'name_en' => 'Excellent', 'description' => null, 'is_visible' => 1],
            ['code' => '1002', 'name_ar' => 'جيد جداً', 'name_en' => 'Very_Good', 'description' => null, 'is_visible' => 1],
            ['code' => '1003', 'name_ar' => 'جيد', 'name_en' => 'Good', 'description' => null, 'is_visible' => 1],
            ['code' => '1004', 'name_ar' => 'مقبول', 'name_en' => 'Acceptable', 'description' => null, 'is_visible' => 1],
            ['code' => '1006', 'name_ar' => 'تجميد', 'name_en' => null, 'description' => null, 'is_visible' => 1],

            ['code' => '2001', 'name_ar' => 'إعادة بكل المقررات', 'name_en' => 'Re_All_Courses', 'description' => null, 'is_visible' => 1],
            ['code' => '2002', 'name_ar' => 'إعادة ببعض المقررات', 'name_en' => 'Re_Some_Courses', 'description' => null, 'is_visible' => 1],

            ['code' => '3001', 'name_ar' => 'بديل', 'name_en' => 'Sub', 'description' => null, 'is_visible' => 1],
            ['code' => '3002', 'name_ar' => 'بديل مؤجل', 'name_en' => null, 'description' => 'غياب الطالب اثناء امتحانات البدائل بعذر', 'is_visible' => 1],
            ['code' => '3004', 'name_ar' => 'بديل+ملحق', 'name_en' => 'Sub_App', 'description' => null, 'is_visible' => 1],

            ['code' => '4001', 'name_ar' => 'ملحق', 'name_en' => 'App', 'description' => null, 'is_visible' => 1],
            ['code' => '4002', 'name_ar' => 'فصل اداري', 'name_en' => null, 'description' => 'ضبط الطالب مرتكبا مخالفة غش في الامتحان أو سلوكيا', 'is_visible' => 1],
            ['code' => '4003', 'name_ar' => 'ملحق مؤجل', 'name_en' => 'ملحق مؤجل', 'description' => 'غياب الطالب اثناء امتحانات الملاحق بعذر', 'is_visible' => 1],
            ['code' => '4004', 'name_ar' => 'فصل اكاديمي', 'name_en' => null, 'description' => null, 'is_visible' => 1],
            ['code' => '4005', 'name_ar' => 'ازالة رسوب', 'name_en' => null, 'description' => null, 'is_visible' => 1],

            ['code' => '5001', 'name_ar' => 'Pass', 'name_en' => 'Pass', 'description' => null, 'is_visible' => 1],
            ['code' => '5002', 'name_ar' => 'Supplementary', 'name_en' => 'Supplementary', 'description' => 'رسوب الطالب في المقرر المعين', 'is_visible' => 1],
            ['code' => '5003', 'name_ar' => 'Substitute', 'name_en' => 'Substitute', 'description' => 'بديل', 'is_visible' => 1],
            ['code' => '5004', 'name_ar' => 'Supplementary & Substitute', 'name_en' => 'Supp & Sub', 'description' => 'ملحق + بديل', 'is_visible' => 1],
            ['code' => '5005', 'name_ar' => 'Discontinue', 'name_en' => 'Discontinue', 'description' => 'فصل', 'is_visible' => 1],

            ['code' => '9999', 'name_ar' => 'توصية افتراضية', 'name_en' => '####', 'description' => null, 'is_visible' => 1],

        ]);
    }
}
