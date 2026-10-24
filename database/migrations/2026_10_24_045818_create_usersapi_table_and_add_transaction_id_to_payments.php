<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usersapi')) {
            Schema::create('usersapi', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 50);
                $table->string('email', 50)->unique();
                $table->string('password', 150);
                $table->string('bank', 50)->nullable();
                $table->string('university', 50)->nullable();
                $table->string('ip', 50)->nullable();
            });
        }

        if (! Schema::hasColumn('payments', 'transaction_id')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->string('transaction_id', 255)->nullable()->index()->after('voucher_no');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('transaction_id');
        });

        Schema::dropIfExists('usersapi');
    }
};
