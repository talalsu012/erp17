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
        Schema::create('reg_campuses', callback: function (Blueprint $table) {
            $table->unsignedBigInteger('code')->primary();
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->boolean('is_visible')->default(false);
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes();      // deleted_at

        });

        Schema::create('reg_faculties', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->primary();

            // العلاقة مع جدول campuses
            $table->unsignedBigInteger('campus_code')->nullable();

            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->boolean('is_visible')->default(false);
            $table->date('start_reg')->nullable();
            $table->date('end_reg')->nullable();
            $table->string('primary_hex')->nullable();
            $table->boolean('results_published')->default(false);
            $table->timestamps();
            $table->softDeletes();      // deleted_at

            // Foreign Key
            $table->foreign('campus_code')
                ->references('code')
                ->on('reg_campuses')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // يمكنك إضافة جداول أخرى مثل الأقسام، البرامج، إلخ بنفس الطريقة
        Schema::create('reg_departments', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->primary();
            $table->unsignedBigInteger('faculty_code')->nullable();
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->boolean('is_visible')->default(false);
            $table->boolean('results_published')->default(false);
            $table->softDeletes();      // deleted_at
            $table->timestamps();

            // Foreign key relation
            $table->foreign('faculty_code')
                ->references('code')
                ->on('reg_faculties')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // يمكنك إضافة جداول أخرى مثل التخصصات، إلخ بنفس الطريقة
        Schema::create('reg_specials', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->primary();
            $table->unsignedBigInteger('department_code');
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->softDeletes();      // deleted_at
            $table->timestamps();

            // Foreign key relation
            $table->foreign('department_code')
                ->references('code')
                ->on('reg_departments')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // حذف التخصص عند حذف القسم
        });

        Schema::create('reg_levels', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->primary();
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->softDeletes();      // deleted_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_levels');
        Schema::dropIfExists('reg_specials');
        Schema::dropIfExists('reg_departments');
        Schema::dropIfExists('reg_faculties');
        Schema::dropIfExists('reg_campuses');

    }
};
