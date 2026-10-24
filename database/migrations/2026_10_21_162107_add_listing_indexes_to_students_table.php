<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COHORT_INDEX = 'students_faculty_level_batch_index';

    private const CREATED_INDEX = 'students_created_at_index';

    /**
     * فهرس مركّب لتصفية الطلاب بالكلية والمستوى والدفعة (أكثر فلاتر القوائم والتقارير استعمالاً)،
     * وفهرس لتاريخ الإنشاء لأن الفرز الافتراضي لقائمة الطلاب عليه.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasIndex('students', self::COHORT_INDEX)) {
                $table->index(['faculty_code', 'level_code', 'batch_code'], self::COHORT_INDEX);
            }

            if (! Schema::hasIndex('students', self::CREATED_INDEX)) {
                $table->index('created_at', self::CREATED_INDEX);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasIndex('students', self::COHORT_INDEX)) {
                $table->dropIndex(self::COHORT_INDEX);
            }

            if (Schema::hasIndex('students', self::CREATED_INDEX)) {
                $table->dropIndex(self::CREATED_INDEX);
            }
        });
    }
};
