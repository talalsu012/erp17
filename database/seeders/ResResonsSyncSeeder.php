<?php

namespace Database\Seeders;

use App\Models\Res\Reson;
use Illuminate\Database\Seeder;

/**
 * يضمن وجود الأسباب التي يعتمد عليها الكود في res_resons ويصحّح بيانات الأسباب القديمة (آمن للتكرار).
 * لا يغيّر رموز الأسباب القديمة المستعملة في الدرجات المحفوظة.
 *
 * الكود يعتمد على: 3001 (غياب بعذر/بديل: REASON_ABSENT) و21 (مستبعد من الحساب: REASON_EXCLUDED).
 *
 * التشغيل: php artisan db:seed --class=ResResonsSyncSeeder
 */
class ResResonsSyncSeeder extends Seeder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function rows(): array
    {
        return [
            // مطلوب من الكود
            ['code' => 3001, 'name_ar' => 'بديل', 'name_en' => 'Substitute', 'description' => 'REASON_ABSENT - غياب بعذر، لا ناجح ولا راسب', 'mark' => null, 'grade' => 'بديل', 'point' => null, 'recom_code' => null],
            ['code' => 21, 'name_ar' => 'نجاح سابق', 'name_en' => 'Pass Old', 'description' => 'REASON_EXCLUDED - المادة مستبعدة من العدد والحساب', 'mark' => null, 'grade' => null, 'point' => null, 'recom_code' => null],

            // أسباب قياسية موثّقة في دليل إدخال الدرجات
            ['code' => 1001, 'name_ar' => 'نجاح', 'name_en' => 'Pass', 'description' => 'يحفظ السبب فقط دون المساس بالدرجة', 'mark' => null, 'grade' => null, 'point' => null, 'recom_code' => null],
            ['code' => 2001, 'name_ar' => 'رسوب', 'name_en' => 'Fail', 'description' => 'رسوب', 'mark' => null, 'grade' => 'رسوب', 'point' => null, 'recom_code' => null],
            ['code' => 2003, 'name_ar' => 'حرمان', 'name_en' => 'Deprivation', 'description' => 'رسوب', 'mark' => null, 'grade' => 'رسوب', 'point' => null, 'recom_code' => null],
            ['code' => 2005, 'name_ar' => 'فصل اداري', 'name_en' => 'Administrative Dismissal', 'description' => 'رسوب', 'mark' => null, 'grade' => 'رسوب', 'point' => null, 'recom_code' => null],

            // تصحيح أسماء الأسباب القديمة (بلا تغيير في الأثر)
            ['code' => 2, 'name_ar' => 'تقرير طبي أثناء الامتحان', 'name_en' => 'Medical report during the exam', 'description' => 'AB', 'mark' => null, 'grade' => 'AB', 'point' => null, 'recom_code' => null],
            ['code' => 18, 'name_ar' => 'غياب بعذر', 'name_en' => 'Absence is excused', 'description' => 'AB - قديم، يعادله 3001', 'mark' => null, 'grade' => 'AB', 'point' => null, 'recom_code' => null],
            ['code' => 20, 'name_ar' => 'حالة غش', 'name_en' => 'Cheating Case', 'description' => 'CC', 'mark' => 0, 'grade' => 'CC', 'point' => 0, 'recom_code' => 21],
            ['code' => 22, 'name_ar' => 'غياب بدون عذر', 'name_en' => 'Absence without excuse', 'description' => 'يُحتسب راسباً (درجة 0)', 'mark' => 0, 'grade' => 'F', 'point' => 0, 'recom_code' => null],
        ];
    }

    public function run(): void
    {
        foreach (self::rows() as $row) {
            $reson = Reson::withTrashed()->firstOrNew(['code' => $row['code']]);
            $reson->fill($row + ['is_visible' => true])->save();

            if ($reson->trashed()) {
                $reson->restore();
            }
        }
    }
}
