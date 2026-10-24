<?php

use App\Reg\MarkApprovalStage;
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
        Schema::table('res_marks', function (Blueprint $table) {
            // add parent reference to support hierarchical lt columns
            $table->enum('approval_stage', array_column(MarkApprovalStage::cases(), 'value'))->default(MarkApprovalStage::Professor->value)->after('note');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approval_stage');
            $table->timestamp('approved_at')->nullable()->after('approved_by');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('res_marks', function (Blueprint $table) {
            // remove added parent column and its foreign key
            $table->dropColumn('approval_stage');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_at');

        });
    }
};
