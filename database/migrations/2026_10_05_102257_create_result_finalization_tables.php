<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('res_finalizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_code');
            $table->unsignedBigInteger('department_code');
            $table->unsignedBigInteger('level_code');
            $table->unsignedBigInteger('batch_code');
            $table->unsignedInteger('version')->default(1);
            $table->string('kind', 12)->default('approval');
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('approved_at');
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('students_count')->default(0);
            $table->unsignedInteger('marks_count')->default(0);
            $table->string('reason', 500)->nullable();
            $table->string('digest', 64);
            $table->string('signature', 64);
            $table->timestamps();

            $table->unique(['faculty_code', 'department_code', 'level_code', 'batch_code', 'version'], 'unique_finalization_version');
            $table->index(['faculty_code', 'department_code', 'level_code', 'batch_code', 'is_active'], 'finalization_active_lookup');
        });

        Schema::create('res_record_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finalization_id')->constrained('res_finalizations');
            $table->unsignedBigInteger('university_no');
            $table->unsignedBigInteger('subject_code');
            $table->string('subject_name_ar')->nullable();
            $table->string('subject_name_en')->nullable();
            $table->decimal('crch', 4, 1)->nullable();
            $table->unsignedBigInteger('faculty_code');
            $table->unsignedBigInteger('department_code')->nullable();
            $table->unsignedBigInteger('special_code')->nullable();
            $table->unsignedBigInteger('level_code');
            $table->unsignedBigInteger('batch_code');
            $table->unsignedTinyInteger('semester');
            $table->unsignedTinyInteger('source_exam_type');
            $table->unsignedSmallInteger('mark')->nullable();
            $table->unsignedSmallInteger('cwork')->nullable();
            $table->string('grade', 20)->nullable();
            $table->decimal('point', 4, 2)->nullable();
            $table->unsignedBigInteger('reson_code')->nullable();
            $table->string('reson_name')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('university_no');
            $table->index(['finalization_id', 'university_no']);
        });

        Schema::create('res_record_recoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finalization_id')->constrained('res_finalizations');
            $table->unsignedBigInteger('university_no');
            $table->unsignedBigInteger('faculty_code');
            $table->unsignedBigInteger('department_code')->nullable();
            $table->unsignedBigInteger('special_code')->nullable();
            $table->unsignedBigInteger('level_code');
            $table->unsignedBigInteger('batch_code');
            $table->unsignedTinyInteger('semester');
            $table->unsignedTinyInteger('examType');
            $table->integer('countSubject')->nullable();
            $table->decimal('sumMarks', 8, 2)->nullable();
            $table->integer('s_pass')->nullable();
            $table->integer('s_fail')->nullable();
            $table->decimal('crch_fail', 6, 1)->nullable();
            $table->integer('s_abs')->nullable();
            $table->decimal('crch', 6, 1)->nullable();
            $table->decimal('points', 8, 2)->nullable();
            $table->decimal('gpa', 5, 2)->nullable();
            $table->decimal('cgpa', 5, 2)->nullable();
            $table->unsignedBigInteger('recom_code')->nullable();
            $table->string('recom_name', 100)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('university_no');
            $table->index(['finalization_id', 'university_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('res_record_recoms');
        Schema::dropIfExists('res_record_marks');
        Schema::dropIfExists('res_finalizations');
    }
};
