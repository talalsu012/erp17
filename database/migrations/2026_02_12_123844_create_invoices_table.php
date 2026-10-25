<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices')) {
            return;
        }

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('name_ar');
            $table->string('invoice_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->integer('sys_type')->nullable();
            $table->unsignedBigInteger('faculty_code')->nullable();
            $table->string('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('bank_pay')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'disputed', 'cancelled', 'paid'])->default('pending');
            $table->string('bank')->nullable();
            $table->timestamp('bankdate')->nullable();
            $table->string('voucher_no')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('faculty_code')->references('code')->on('reg_faculties')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
