<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_INDEX = 'users_std_university_no_unique';

    /**
     * جدول رموز استعادة كلمة مرور الطلاب منفصل عن جدول الإدارة (مفتاحه البريد فقط)،
     * وفهرس unique على الرقم الجامعي يمنع حسابين لطالب واحد عند التسجيل المتزامن.
     */
    public function up(): void
    {
        if (! Schema::hasTable('student_password_reset_tokens')) {
            Schema::create('student_password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (Schema::hasIndex('users_std', self::UNIQUE_INDEX)) {
            return;
        }

        $duplicates = DB::table('users_std')
            ->select('university_no')
            ->whereNotNull('university_no')
            ->groupBy('university_no')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('university_no');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'يوجد أكثر من حساب لنفس الرقم الجامعي في users_std، عالجها يدوياً قبل الترحيل: '.$duplicates->implode(', ')
            );
        }

        Schema::table('users_std', function (Blueprint $table) {
            $table->unique('university_no', self::UNIQUE_INDEX);
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('users_std', self::UNIQUE_INDEX)) {
            Schema::table('users_std', function (Blueprint $table) {
                $table->dropUnique(self::UNIQUE_INDEX);
            });
        }

        Schema::dropIfExists('student_password_reset_tokens');
    }
};
