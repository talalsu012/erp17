<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResResonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resons = [

            [
                'code' => '1001',
                'name_ar' => 'نجاح',
                'name_en' => 'Pass',

                'description' => null,
                'is_visible' => 1,
            ],
            [
                'code' => '1002',
                'name_ar' => 'نجاح سابق',
                'name_en' => null,

                'description' => null,
                'is_visible' => 1,
            ],
            [
                'code' => '2001',
                'name_ar' => 'رسوب',
                'name_en' => 'Fail',

                'description' => 'رسوب',
                'is_visible' => 1,
            ],
            [
                'code' => '2002',
                'name_ar' => 'غياب',
                'name_en' => 'Absence without leave',

                'description' => 'رسوب',
                'is_visible' => 1,
            ],
            [
                'code' => '2003',
                'name_ar' => 'حرمان',
                'name_en' => null,

                'description' => 'رسوب',
                'is_visible' => 1,
            ],
            [
                'code' => '2004',
                'name_ar' => 'حالة غش',
                'name_en' => null,

                'description' => 'رسوب',
                'is_visible' => 1,
            ],
            [
                'code' => '2005',
                'name_ar' => 'فصل اداري',
                'name_en' => null,

                'description' => 'رسوب',
                'is_visible' => 1,
            ],
            [
                'code' => '3001',
                'name_ar' => 'بديل',
                'name_en' => 'Substitute',

                'description' => 'بديل',
                'is_visible' => 1,
            ],

        ];

        // الأسباب التي رمزها أكبر من 2000 تلغي الدرجة ويظهر وصفها مكان التقدير (السلوك الأصلي)
        DB::table('res_resons')->insert(array_map(fn (array $reson): array => $reson + [
            'mark' => null,
            'grade' => (int) $reson['code'] > 2000 ? $reson['description'] : null,
            'point' => null,
            'recom_code' => null,
        ], $resons));
    }
}
