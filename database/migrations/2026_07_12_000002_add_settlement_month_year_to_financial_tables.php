<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Decouple financial request approval from the monthly_periods workspace.
     * Admins now select settlement month+year directly; no workspace needs to exist
     * ahead of time. The import step (Batch 5) will match on delegate+month+year.
     */
    public function up(): void
    {
        Schema::table('financial_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('settlement_month')->nullable()->after('approved_notes');
            $table->unsignedSmallInteger('settlement_year')->nullable()->after('settlement_month');
        });

        Schema::table('pending_financial_entries', function (Blueprint $table) {
            $table->unsignedTinyInteger('settlement_month')->nullable()->after('monthly_period_id');
            $table->unsignedSmallInteger('settlement_year')->nullable()->after('settlement_month');
        });

        // Make monthly_period_id nullable so approval no longer requires an open workspace.
        // The FK index is preserved; MySQL allows nullable FK columns.
        DB::statement('ALTER TABLE pending_financial_entries MODIFY COLUMN monthly_period_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('financial_requests', function (Blueprint $table) {
            $table->dropColumn(['settlement_month', 'settlement_year']);
        });

        Schema::table('pending_financial_entries', function (Blueprint $table) {
            $table->dropColumn(['settlement_month', 'settlement_year']);
        });

        DB::statement('ALTER TABLE pending_financial_entries MODIFY COLUMN monthly_period_id BIGINT UNSIGNED NOT NULL');
    }
};
