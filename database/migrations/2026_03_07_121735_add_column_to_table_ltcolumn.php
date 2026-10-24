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
        Schema::table('reg_ltcolumns', function (Blueprint $table) {
            // add parent reference to support hierarchical lt columns
            $table->unsignedBigInteger('parent_code')->nullable()->after('group_slug');

            // foreign key linking to self
            $table->foreign('parent_code')
                ->references('code')
                ->on('reg_ltcolumns')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reg_ltcolumns', function (Blueprint $table) {
            // remove added parent column and its foreign key
            $table->dropForeign(['parent_code']);
            $table->dropColumn('parent_code');
        });
    }
};
