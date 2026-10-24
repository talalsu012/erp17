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
        Schema::create('reg_ltcolumns', function (Blueprint $table) {
            $table->unsignedBigInteger('code')->primary('code_primary');
            $table->string('name_ar')->unique('name_ar_unique');
            $table->string('name_en');
            $table->integer('grouped')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->string('group_slug')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();      // deleted_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_ltcolumns');
    }
};
