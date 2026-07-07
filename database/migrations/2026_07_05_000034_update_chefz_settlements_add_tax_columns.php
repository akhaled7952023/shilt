<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            // Rate snapshot (same format as system settings: 0.1500 = 15%)
            $table->decimal('chefz_tax_rate', 6, 4)->default(0)->after('cash_collected');
            $table->decimal('chefz_tax_amount', 10, 2)->default(0)->after('chefz_tax_rate');
            $table->decimal('company_share_rate', 6, 4)->default(0)->after('chefz_tax_amount');
            $table->decimal('company_share_amount', 10, 2)->default(0)->after('company_share_rate');
        });
    }

    public function down(): void
    {
        Schema::table('chefz_delegate_settlements', function (Blueprint $table) {
            $table->dropColumn([
                'chefz_tax_rate', 'chefz_tax_amount',
                'company_share_rate', 'company_share_amount',
            ]);
        });
    }
};
