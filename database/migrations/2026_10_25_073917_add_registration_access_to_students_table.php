<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تخصيص فردي لتسجيل الطالب: open (مفتوح له) أو closed (محجوب عنه)، والفارغ = حسب فترة open-regs.
     * reg_access_at وقت المنح، ومنه تُحسب مدفوعات الطالب المفتوح له التسجيل خارج الفترة.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'reg_access')) {
                $table->string('reg_access', 10)->nullable();
            }

            if (! Schema::hasColumn('students', 'reg_access_at')) {
                $table->timestamp('reg_access_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            foreach (['reg_access', 'reg_access_at'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
