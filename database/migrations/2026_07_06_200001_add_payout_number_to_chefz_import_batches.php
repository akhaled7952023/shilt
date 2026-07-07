<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chefz_import_batches', function (Blueprint $table) {
            // 1 = First Payout, 2 = Second Payout
            $table->unsignedTinyInteger('payout_number')->default(1)->after('monthly_period_id');
        });
    }

    public function down(): void
    {
        Schema::table('chefz_import_batches', function (Blueprint $table) {
            $table->dropColumn('payout_number');
        });
    }
};
