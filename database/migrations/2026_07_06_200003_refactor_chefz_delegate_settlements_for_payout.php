<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns
        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->unsignedTinyInteger('payout_number')->default(1)->after('delegate_id');
            $table->decimal('bonus_total', 10, 2)->default(0)->after('platform_compensations');
            $table->decimal('positive_bonus', 10, 2)->default(0)->after('bonus_total');
        });

        // Step 2: Add NEW unique constraint first.
        // MySQL requires the old unique index as a support index for the monthly_period_id FK.
        // The new composite (monthly_period_id, delegate_id, payout_number) satisfies that FK,
        // so it must exist before we drop the old one.
        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->unique(
                ['monthly_period_id', 'delegate_id', 'payout_number'],
                'chefz_settlements_period_delegate_payout_uq'
            );
        });

        // Step 3: Now safe to drop the old unique index and obsolete columns
        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->dropUnique('chefz_settlements_period_delegate_uq');
            $table->dropColumn(['housing_allowance', 'entitlements_manual', 'grants_manual', 'cash_collected']);
        });
    }

    public function down(): void
    {
        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->decimal('housing_allowance', 10, 2)->default(0);
            $table->decimal('entitlements_manual', 10, 2)->default(0);
            $table->decimal('grants_manual', 10, 2)->default(0);
            $table->decimal('cash_collected', 12, 2)->default(0);
        });

        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->unique(
                ['monthly_period_id', 'delegate_id'],
                'chefz_settlements_period_delegate_uq'
            );
        });

        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->dropUnique('chefz_settlements_period_delegate_payout_uq');
            $table->dropColumn(['payout_number', 'bonus_total', 'positive_bonus']);
        });
    }
};
