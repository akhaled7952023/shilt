<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chefz_orders', function (Blueprint $table) {
            $table->unsignedTinyInteger('payout_number')->default(1)->after('import_batch_id');
            $table->decimal('bonus_amount', 10, 2)->default(0)->after('compensation');
            $table->text('bonus_note')->nullable()->after('bonus_amount');
        });
    }

    public function down(): void
    {
        Schema::table('chefz_orders', function (Blueprint $table) {
            $table->dropColumn(['payout_number', 'bonus_amount', 'bonus_note']);
        });
    }
};
