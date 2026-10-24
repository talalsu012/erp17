<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('payments', function (Blueprint $table) {
            // المعرف الأساسي
            $table->id();

            // بيانات الدفع الأساسية
            $table->unsignedBigInteger('university_no')->index();
            $table->float('amount');
            $table->string('currency', 255)->default('SDG');
            $table->date('bankdate')->default('2024-10-21');
            $table->string('bank', 255)->default('LOC');
            $table->string('branch', 255)->nullable();
            $table->string('voucher_no', 255)->nullable();

            // الأكواد المرجعية
            $table->unsignedBigInteger('batch_code')->nullable()->index();
            $table->unsignedBigInteger('level_code')->nullable()->index();
            $table->string('sys_type', 255)->nullable();

            // التوقيعات الزمنية والحذف الناعم
            $table->softDeletes();
            $table->timestamps();

            // العلاقات (Foreign Keys)
            $table->foreign('university_no')
                ->references('university_no')
                ->on('students')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('batch_code')
                ->references('code')
                ->on('reg_ltcolumns')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('level_code')
                ->references('code')
                ->on('reg_levels')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
