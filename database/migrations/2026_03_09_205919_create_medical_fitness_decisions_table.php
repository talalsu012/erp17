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
        Schema::create('medical_fitness_decisions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('university_no')
                ->constrained('students', 'university_no')
                ->cascadeOnUpdate()
                ->cascadeOnDelete('SET NULL');
            $table->enum('medical_fitness', ['fit', 'unfit'])->default('fit');
            $table->enum('college_interview', ['passed', 'failed'])->default('passed');
            $table->enum('final_decision', ['accepted', 'rejected', 'pending'])->default('pending');
            $table->text('medical_notes')->nullable();
            $table->text('interview_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('decision_date');
            $table->foreignId('decided_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_fitness_decisions');
    }
};
