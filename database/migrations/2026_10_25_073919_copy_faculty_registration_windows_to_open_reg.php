<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * open-regs صار المصدر الوحيد لفترات التسجيل: تُنسخ فترة كل كلية (start_reg..end_reg) إلى صف open-regs
     * (كل الأقسام والمستويات والدفعات) إن لم يكن للكلية أي صف، فلا تتغير حسابات الرسوم الحالية.
     * أعمدة الكلية القديمة تبقى في القاعدة دون استعمال.
     */
    public function up(): void
    {
        if (! Schema::hasTable('reg_open_reg') || ! Schema::hasColumn('reg_faculties', 'start_reg')) {
            return;
        }

        $now = now();

        DB::table('reg_faculties')
            ->whereNotNull('start_reg')
            ->whereNotNull('end_reg')
            ->whereNull('deleted_at')
            ->orderBy('code')
            ->get(['code', 'start_reg', 'end_reg'])
            ->each(function (object $faculty) use ($now): void {
                $hasWindow = DB::table('reg_open_reg')->where('faculty_code', $faculty->code)->whereNull('deleted_at')->exists();

                if (! $hasWindow) {
                    DB::table('reg_open_reg')->insert([
                        'faculty_code' => $faculty->code,
                        'start_date' => substr((string) $faculty->start_reg, 0, 10),
                        'end_date' => substr((string) $faculty->end_reg, 0, 10),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // النسخ لا يُعكس: الصفوف المنسوخة قد تكون عُدّلت من open-regs.
    }
};
