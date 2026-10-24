<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('res_resons', function (Blueprint $table) {
            $table->unsignedSmallInteger('mark')->nullable()->after('description');
            $table->string('grade', 20)->nullable()->after('mark');
            $table->decimal('point', 4, 2)->nullable()->after('grade');
            $table->unsignedBigInteger('recom_code')->nullable()->after('point');

            $table->foreign('recom_code')
                ->references('code')->on('res_ltrecoms')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        DB::table('res_resons')
            ->where('code', '>', 2000)
            ->whereNull('grade')
            ->whereNotNull('description')
            ->update(['grade' => DB::raw('description')]);
    }

    public function down(): void
    {
        Schema::table('res_resons', function (Blueprint $table) {
            $table->dropForeign(['recom_code']);
            $table->dropColumn(['mark', 'grade', 'point', 'recom_code']);
        });
    }
};
