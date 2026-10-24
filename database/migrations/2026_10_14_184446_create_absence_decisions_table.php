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
        Schema::create('res_absence_decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('university_no');
            $table->unsignedBigInteger('subject_code');
            $table->unsignedBigInteger('batch_code');
            $table->unsignedTinyInteger('semester');

            $table->unsignedSmallInteger('total_sessions')->default(0);
            $table->unsignedSmallInteger('absent_sessions')->default(0);
            $table->decimal('absence_pct', 5, 2)->default(0);

            $table->string('status', 30)->default('normal');
            $table->timestamp('warned_at')->nullable();

            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('note', 500)->nullable();

            $table->timestamps();

            $table->unique(['university_no', 'subject_code', 'batch_code', 'semester'], 'unique_absence_decision');
            $table->index(['status', 'batch_code'], 'absence_status_lookup');
            $table->foreign('university_no')->references('university_no')->on('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('subject_code')->references('code')->on('subjects')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('res_absence_decisions');
    }
};
