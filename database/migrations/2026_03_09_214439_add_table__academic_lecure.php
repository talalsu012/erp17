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
        Schema::create('att_grouping_student', function (Blueprint $table) {
            $table->unsignedBigInteger('grouping_id');
            $table->unsignedBigInteger('university_no')->nullable();
            $table->unsignedBigInteger('batch_code')->nullable();
            $table->foreignId('room_id')->constrained('att_rooms');

            $table->foreign('grouping_id')->references('id')->on('att_grouping')->onDelete('cascade');
            $table->foreign('university_no')->references('university_no')->on('students')->onDelete('cascade');
            $table->foreign('batch_code')->references('code')->on('reg_ltcolumns')->onUpdate('cascade')->onDelete('RESTRICT');

            $table->primary(['grouping_id', 'university_no', 'room_id']);
            $table->timestamps();
        });

        Schema::create('att_lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_code')->constrained('subjects', 'code');
            $table->foreignId('staff_id')->constrained('att_staff');
            $table->foreignId('room_id')->constrained('att_rooms');
            $table->foreignId('grouping_id')->constrained('att_grouping');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('day_of_week'); // Monday, Tuesday, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('att_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained('att_lectures');
            $table->date('date');
            $table->time('time');
            $table->foreignId('university_no')->constrained('students', 'university_no');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('att_staff');
            $table->timestamps();

            $table->unique(['lecture_id', 'date', 'university_no']);
        });

        Schema::create('att_grouping_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('att_rooms')->onDelete('cascade');
            $table->foreignId('grouping_id')->constrained('att_grouping')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('att_grouping_student');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('att_lectures');
        Schema::enableForeignKeyConstraints();

        Schema::dropIfExists('att_attendances');
        Schema::dropIfExists('att_grouping_rooms');
    }
};
