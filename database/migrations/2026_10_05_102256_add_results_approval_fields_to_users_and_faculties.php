<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('approval_code_hash', 64)->nullable()->unique()->after('password');
        });

        Schema::table('reg_faculties', function (Blueprint $table) {
            $table->foreignId('results_approver_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reg_faculties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('results_approver_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['approval_code_hash']);
            $table->dropColumn('approval_code_hash');
        });
    }
};
