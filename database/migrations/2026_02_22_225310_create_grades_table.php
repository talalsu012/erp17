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
        Schema::create('res_grades', function (Blueprint $table) {

            $table->id();

            $table->string('title', 10);

            $table->string('titlear', 10)
                ->default('ar');

            $table->tinyInteger('upper');
            $table->tinyInteger('lower');

            $table->float('point')
                ->default(0);

            $table->unsignedBigInteger('plan')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('res_grades');
    }
};
