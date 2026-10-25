<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users_std')) {
            return;
        }

        Schema::create('users_std', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique('users_email_unique');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('avatar_url', 100)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('university_no')->nullable()->index('users_std_university_no_foreign');
            $table->string('name_en', 200)->nullable();
            $table->string('first_name', 150)->nullable();
            $table->string('second_name', 150)->nullable();
            $table->string('third_name', 150)->nullable();
            $table->string('fourth_name', 150)->nullable();
            $table->string('school', 150)->nullable();
            $table->string('percent', 20)->nullable();
            $table->string('nationaltyno', 30)->nullable()->unique('users_std_nationaltyno_unique');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('nationality_code')->nullable()->index('users_std_nationality_code_foreign');
            $table->unsignedBigInteger('state_code')->nullable()->index('users_std_state_code_foreign');
            $table->string('city')->nullable();
            $table->string('localty')->nullable();
            $table->string('block')->nullable();
            $table->string('home_no')->nullable();
            $table->string('mobile')->nullable();
            $table->string('social_media')->nullable();
            $table->string('activites')->nullable();
            $table->string('skill')->nullable();
            $table->string('parent')->nullable();
            $table->boolean('parent_alive')->nullable();
            $table->string('parent_job')->nullable();
            $table->string('parent_address')->nullable();
            $table->string('parent_contacts')->nullable();
            $table->string('mother')->nullable();
            $table->boolean('mother_alive')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('mother_address')->nullable();
            $table->string('mother_contacts')->nullable();
            $table->string('brothers')->nullable();
            $table->json('files')->nullable();

            $table->foreign('university_no', 'FK_users_std_students')->references('university_no')->on('students')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_std');
    }
};
