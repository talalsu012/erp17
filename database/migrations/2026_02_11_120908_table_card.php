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
        Schema::create('reg_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('university_no')->index();
            $table->string('card_number')->unique();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'lost', 'expired', 'demo'])
                ->nullable()
                ->default('active');
            $table->boolean('is_visible')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('university_no')
                ->references('university_no')
                ->on('students')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_cards');
    }
};
