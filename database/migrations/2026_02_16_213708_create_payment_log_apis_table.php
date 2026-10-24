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
        Schema::create('payment_log_apis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_no')->index();
            $table->string('name', 255)->nullable();
            $table->float('amount');
            $table->date('bankdate')->default('2024-10-21');
            $table->string('voucher_no', 255)->nullable();
            $table->string('bank', 255)->default('LOC');
            $table->string('branch', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('response_msg', 1000)->nullable();
            $table->integer('response_code')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_log_apis');
    }
};
