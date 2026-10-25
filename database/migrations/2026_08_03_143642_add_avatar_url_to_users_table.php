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
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');

        if (Schema::hasColumn('users', $avatarColumn)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($avatarColumn) {
            $table->string($avatarColumn)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar_url');

        if (Schema::hasColumn('users', $avatarColumn)) {
            Schema::table('users', function (Blueprint $table) use ($avatarColumn) {
                $table->dropColumn($avatarColumn);
            });
        }
    }
};
