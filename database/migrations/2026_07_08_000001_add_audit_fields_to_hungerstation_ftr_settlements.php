<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hungerstation_ftr_settlements', function (Blueprint $table) {
            $table->unsignedSmallInteger('working_days')->nullable()->after('total_orders');
            $table->string('inactive_reason', 50)->nullable()->after('working_days');
            $table->decimal('google_distance_payable', 10, 3)->nullable()->after('rider_balance');
        });
    }

    public function down(): void
    {
        Schema::table('hungerstation_ftr_settlements', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'inactive_reason', 'google_distance_payable']);
        });
    }
};
