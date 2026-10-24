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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_field_permissions', function (Blueprint $table) {
            // $table->id();

            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained()->cascadeOnDelete();

            $table->primary(['role_id', 'field_id']);
        });
    }

    /**
     * Reverse the migrations.
     * php artisan migrate --path=/database/migrations/2026_02_10_084843_create_role_field_permissions_table.php
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
        Schema::dropIfExists('role_field_permissions');
    }
};
