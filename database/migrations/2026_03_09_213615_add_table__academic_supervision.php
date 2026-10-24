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
        Schema::create('att_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('degree');
            $table->date('date_degree');
            $table->string('specialization');
            $table->enum('appointment', ['basic', 'temp']);
            $table->foreignId('gender_code')
                ->nullable()
                ->constrained('reg_ltcolumns', 'code')
                ->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('att_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('campus_code')
                ->nullable()->constrained('reg_campuses', 'code')
                ->cascadeOnUpdate();
            $table->integer('capacity');
            $table->enum('type', ['theoretical', 'practical']);
            $table->timestamps();
        });

        Schema::create('att_grouping', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('att_grouping');
        });

        Schema::create('att_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('subject_code')
                ->nullable()->constrained('subjects', 'code')
                ->cascadeOnUpdate();

            $table->unsignedBigInteger('group_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('day', ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday']);
            $table->unsignedBigInteger('staff_id');
            $table->boolean('flag_countenuse')->default(false);
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('att_rooms');
            $table->foreign('group_id')->references('id')->on('att_grouping');
            $table->foreign('staff_id')->references('id')->on('att_staff');
        });

        Schema::create('att_staff_subject', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id');
            $table->foreignId('subject_code')
                ->nullable()->constrained('subjects', 'code')
                ->cascadeOnUpdate();

            $table->foreign('staff_id')->references('id')->on('att_staff')->onDelete('cascade');

        });

        Schema::create('att_attendance_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('staff_id');
            $table->enum('excutive', ['yes', 'no']);
            $table->time('actual_excutive_start_time')->nullable();
            $table->time('actual_excutive_end_time')->nullable();
            $table->timestamps();
            $table->foreign('attendance_id')->references('id')->on('att_attendance');
            $table->foreign('staff_id')->references('id')->on('att_staff');
        });

        Schema::create('att_attendance_student', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id');
            $table->unsignedBigInteger('student_id');

            $table->foreign('student_id')->references('university_no')->on('students')->onDelete('cascade');

            $table->foreign('attendance_id')->references('id')->on('att_attendance')->onDelete('cascade');
            $table->enum('status', ['present', 'absent', 'late']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('att_attendance_student');
        Schema::dropIfExists('att_attendance_staff');
        Schema::dropIfExists('att_staff_subject');
        Schema::dropIfExists('att_attendance');
        Schema::dropIfExists('att_grouping');
        Schema::dropIfExists('att_rooms');
        Schema::dropIfExists('att_staff');
    }
};
