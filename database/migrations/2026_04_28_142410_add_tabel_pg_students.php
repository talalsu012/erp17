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
        Schema::create('pg_students', function (Blueprint $table) {
            $table->id();
            $table->string('full_name_ar'); // الاسم رباعي

            $table->string('full_name_en')->nullable();
            $table->string('national_no', 11)->unique(); // الرقم الوطني
            $table->string('nationality');
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('job_title')->nullable();
            $table->string('photo')->nullable(); // الصورة الفوتوغرافية
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pg_students');
    }
};
