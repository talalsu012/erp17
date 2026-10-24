<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * مسارات المرفقات الاختيارية للطالب (الرقم الوطني، إثبات الشخصية، الشهادة السودانية) بصيغة JSON.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users_std', 'files')) {
            Schema::table('users_std', function (Blueprint $table): void {
                $table->json('files')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users_std', 'files')) {
            Schema::table('users_std', function (Blueprint $table): void {
                $table->dropColumn('files');
            });
        }
    }
};
