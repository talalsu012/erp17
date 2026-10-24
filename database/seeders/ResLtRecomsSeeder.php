<?php

namespace Database\Seeders;

use App\Models\Res\LtRecom;
use Illuminate\Database\Seeder;

/**
 * يضمن وجود القرارات التي يستعملها الكود في res_ltrecoms (آمن للتكرار).
 * لا يمسّ القرارات القديمة (1 إلى 30)، ويصحّح معنى 1006 إلى «تجميد» (FROZEN_RECOM).
 *
 * التشغيل: php artisan db:seed --class=ResLtRecomsSeeder
 */
class ResLtRecomsSeeder extends Seeder
{
    /**
     * @return array<int, array{code: int, name_ar: string, name_en: string, description: string}>
     */
    public static function rows(): array
    {
        return [
            ['code' => 1005, 'name_ar' => 'نجاح', 'name_en' => 'Passed', 'description' => 'RecomCode::Pass - لا رسوب ولا غياب'],
            ['code' => 1006, 'name_ar' => 'تجميد', 'name_en' => 'Frozen', 'description' => 'ResultSheetService::FROZEN_RECOM - طالب مجمّد'],
            ['code' => 2001, 'name_ar' => 'إعادة بكل المقررات', 'name_en' => 'Repeat All Courses', 'description' => 'RecomCode::RepeatAll - رسوب أكثر من 70% في الملحق'],
            ['code' => 2002, 'name_ar' => 'إعادة ببعض المقررات', 'name_en' => 'Repeat Some Courses', 'description' => 'RecomCode::RepeatSome - رسوب من 50% إلى 75%'],
            ['code' => 3001, 'name_ar' => 'بديل', 'name_en' => 'Substitute Exam', 'description' => 'RecomCode::Substitute - غياب بعذر فقط'],
            ['code' => 3004, 'name_ar' => 'ملحق / بديل', 'name_en' => 'Substitute and Supplementary', 'description' => 'RecomCode::SubstituteAndSupplementary - غياب بعذر مع رسوب'],
            ['code' => 4001, 'name_ar' => 'ملحق', 'name_en' => 'Supplementary', 'description' => 'RecomCode::Supplementary - رسوب أقل من 50% من الساعات'],
            ['code' => 4002, 'name_ar' => 'فصل إداري', 'name_en' => 'Administrative Dismissal', 'description' => 'ShowAbsentCode::AdministrativeLeave - يُحسب ضمن مرات الامتحان من الخارج'],
            ['code' => 4004, 'name_ar' => 'فصل', 'name_en' => 'Academic Dismissal', 'description' => 'RecomCode::AcademicDismissal - رسوب 75% فأكثر أو إعادة سابقة'],
            ['code' => 4005, 'name_ar' => 'إزالة رسوب', 'name_en' => 'Remove Fail', 'description' => 'RecomCode::RemoveFail - مادة راسبة واحدة في الملحق'],
            ['code' => 9999, 'name_ar' => 'توصية افتراضية', 'name_en' => 'Incomplete', 'description' => 'RecomCode::Incomplete - درجات المواد غير مكتملة'],
        ];
    }

    public function run(): void
    {
        foreach (self::rows() as $row) {
            LtRecom::withTrashed()->updateOrCreate(
                ['code' => $row['code']],
                $row + ['is_visible' => true, 'deleted_at' => null],
            );
        }
    }
}
