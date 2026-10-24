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
        Schema::create('admission_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('university_no')->unique();
            $table->string('stage', 30)->default('awaiting_doctor');

            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('medical_fitness', 10)->nullable();
            $table->text('medical_notes')->nullable();
            $table->foreignId('medical_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('medical_at')->nullable();

            $table->string('committee_decision', 10)->nullable();
            $table->text('committee_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('committee_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('committee_at')->nullable();

            $table->decimal('assigned_fees', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->foreignId('fees_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fees_at')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('account_created_at')->nullable();

            $table->text('dean_notes')->nullable();
            $table->foreignId('dean_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dean_at')->nullable();

            $table->timestamps();

            $table->index('stage');
            $table->foreign('university_no')->references('university_no')->on('students')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_workflows');
    }
};
