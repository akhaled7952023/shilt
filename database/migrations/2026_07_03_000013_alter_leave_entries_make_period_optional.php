<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK constraint then make both columns nullable.
        // days_count is calculated dynamically; monthly_period_id belongs to Phase 3.
        DB::statement('ALTER TABLE leave_entries DROP FOREIGN KEY leave_entries_monthly_period_id_foreign');
        DB::statement('ALTER TABLE leave_entries MODIFY COLUMN monthly_period_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE leave_entries MODIFY COLUMN days_count SMALLINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE leave_entries MODIFY COLUMN monthly_period_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE leave_entries MODIFY COLUMN days_count SMALLINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE leave_entries ADD CONSTRAINT leave_entries_monthly_period_id_foreign FOREIGN KEY (monthly_period_id) REFERENCES monthly_periods(id) ON DELETE RESTRICT');
    }
};
