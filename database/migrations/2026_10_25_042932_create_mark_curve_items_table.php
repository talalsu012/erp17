<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('res_mark_curve_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mark_curve_id')->constrained('res_mark_curves')->cascadeOnDelete();
            $table->unsignedBigInteger('mark_id')->index();
            $table->unsignedBigInteger('university_no');
            $table->integer('old_mark')->nullable();
            $table->string('old_grade', 20)->nullable();
            $table->float('old_point')->nullable();
            $table->integer('new_mark')->nullable();
            $table->string('new_grade', 20)->nullable();
            $table->float('new_point')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('res_mark_curve_items');
    }
};
