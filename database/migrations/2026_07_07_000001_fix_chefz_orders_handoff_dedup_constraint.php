<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chefz_orders', function (Blueprint $table) {
            // Old constraint treated (period, order_id) as globally unique, which incorrectly
            // dropped the second row when Chefz legitimately assigns the same order to two
            // different drivers (e.g. breakdown + reassignment in the same export file).
            // New constraint: (period, payout, driver, order_id) — prevents true same-driver
            // duplicates while allowing cross-driver handoff rows to coexist.
            $table->dropUnique('chefz_orders_period_order_uq');
            $table->unique(
                ['monthly_period_id', 'payout_number', 'raw_driver_id', 'order_id_platform'],
                'chefz_orders_period_payout_driver_order_uq'
            );
        });
    }

    public function down(): void
    {
        Schema::table('chefz_orders', function (Blueprint $table) {
            $table->dropUnique('chefz_orders_period_payout_driver_order_uq');
            $table->unique(
                ['monthly_period_id', 'order_id_platform'],
                'chefz_orders_period_order_uq'
            );
        });
    }
};
