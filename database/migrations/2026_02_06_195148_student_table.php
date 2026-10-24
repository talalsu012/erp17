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
        // ========================================
        // TABLE: students
        // ========================================
        Schema::create('students', function (Blueprint $table) {
            $table->unsignedBigInteger('university_no')->primary();
            $table->string('name_ar', 200)->nullable();
            $table->string('name_en', 200)->nullable();
            $table->string('first_name', 50)->nullable();
            $table->string('second_name', 50)->nullable();
            $table->string('third_name', 50)->nullable();
            $table->string('fourth_name', 50)->nullable();

            $table->unsignedBigInteger('campus_code')->nullable();
            $table->unsignedBigInteger('faculty_code')->nullable();
            $table->unsignedBigInteger('department_code')->nullable();
            $table->unsignedBigInteger('special_code')->nullable();
            $table->unsignedBigInteger('level_code')->nullable();
            $table->unsignedBigInteger('admission_code')->nullable();
            $table->unsignedBigInteger('stady_code')->nullable();
            $table->unsignedBigInteger('academic_year_code')->nullable();
            $table->unsignedBigInteger('status_code')->nullable();
            $table->unsignedBigInteger('batch_code')->nullable();

            $table->string('nationaltyno', 30)->unique()->nullable();
            $table->string('id_photo')->nullable();
            $table->unsignedBigInteger('gender_code')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('nationality_code')->nullable();
            $table->unsignedBigInteger('state_code')->nullable();
            $table->string('city')->nullable();
            $table->string('localty')->nullable();
            $table->string('block')->nullable();
            $table->string('home_no')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->default('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('mobile')->nullable();
            $table->string('social_media')->nullable();
            $table->string('activites')->nullable();
            $table->string('skill')->nullable();
            $table->string('parent')->nullable();
            $table->boolean('parent_alive')->nullable();
            $table->string('parent_job')->nullable();
            $table->string('parent_address')->nullable();
            $table->string('parent_contacts')->nullable();
            $table->string('mother')->nullable();
            $table->boolean('mother_alive')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('mother_address')->nullable();
            $table->string('mother_contacts')->nullable();
            $table->string('brothers')->nullable();
            $table->enum('currency', ['SDG', 'USD'])->default('SDG');
            $table->unsignedBigInteger('installment_code')->nullable();
            $table->unsignedInteger('discount_percent')->default(0);
            $table->double('fees')->default(0);
            $table->double('reg_fees')->default(0);
            $table->double('fees1')->default(0);
            $table->double('fees2')->default(0);
            $table->double('fees3')->default(0);
            $table->double('fees4')->default(0);
            $table->double('fees5')->default(0);
            $table->double('fees6')->default(0);
            $table->double('fees7')->default(0);
            $table->double('fees8')->default(0);
            $table->double('fees9')->default(0);
            $table->double('fees10')->default(0);
            $table->double('pay1')->default(0);
            $table->double('pay2')->default(0);
            $table->double('pay3')->default(0);
            $table->double('pay4')->default(0);
            $table->double('all_fees')->default(0);
            $table->double('pay')->default(0);
            $table->double('xmod')->default(0);
            $table->integer('xstatus')->default(1001);

            $table->timestamps();
            $table->softDeletes();
            $table->string('indexno')->nullable();
            $table->longText('note')->nullable();
            $table->boolean('is_visible')->default(1);

            // ========================================
            // FOREIGN KEYS
            // ========================================
            $table->foreign('faculty_code')->references('code')->on('reg_faculties')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('level_code')->references('code')->on('reg_levels')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('academic_year_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('admission_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('campus_code')->references('code')->on('reg_campuses')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('department_code')->references('code')->on('reg_departments')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('gender_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('installment_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('nationality_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('special_code')->references('code')->on('reg_specials')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('stady_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('state_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('status_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
            $table->foreign('batch_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
