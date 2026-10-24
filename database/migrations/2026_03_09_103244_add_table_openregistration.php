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
        Schema::create('reg_open_reg', function (Blueprint $table) {

            $table->id();

            // faculty relationship
            $table->unsignedBigInteger('faculty_code')->nullable();
            $table->foreign('faculty_code')->references('code')->on('reg_faculties')->onUpdate('CASCADE')->onDelete('RESTRICT');

            // department relationship
            $table->unsignedBigInteger('department_code')->nullable();
            $table->foreign('department_code')->references('code')->on('reg_departments')->onUpdate('CASCADE')->onDelete('RESTRICT');

            // level relationship
            $table->unsignedBigInteger('level_code')->nullable();
            $table->foreign('level_code')->references('code')->on('reg_levels')->onUpdate('CASCADE')->onDelete('RESTRICT');

            // batch relationship
            $table->unsignedBigInteger('batch_code')->nullable();
            $table->foreign('batch_code')->references('code')->on('reg_ltcolumns')->onUpdate('CASCADE')->onDelete('RESTRICT');

            // dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_open_reg');
    }
};
