<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reg_level_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('level_code')->index();
            $table->timestamps();

            $table->unique(['user_id', 'level_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reg_level_user');
    }
};
