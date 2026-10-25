<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('res_mark_curves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_code')->index();
            $table->unsignedBigInteger('batch_code');
            $table->unsignedBigInteger('level_code');
            $table->unsignedTinyInteger('exam_type')->default(1);
            $table->string('method', 20)->comment('range: نطاق ± درجات، stddev: الانحراف المعياري');
            $table->json('params');
            $table->string('note', 250)->nullable();
            $table->unsignedInteger('affected_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reverted_at')->nullable();
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('res_mark_curves');
    }
};
