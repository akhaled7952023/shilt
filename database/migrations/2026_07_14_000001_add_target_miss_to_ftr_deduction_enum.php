<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE hungerstation_ftr_delegate_deductions
            MODIFY COLUMN deduction_type ENUM(
                'fuel','iqama','advance','loan','vehicle',
                'app_penalty','company_penalty','traffic_violation','other',
                'housing_allowance','transport_allowance','food_allowance','other_benefit',
                'target_miss'
            ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE hungerstation_ftr_delegate_deductions
            MODIFY COLUMN deduction_type ENUM(
                'fuel','iqama','advance','loan','vehicle',
                'app_penalty','company_penalty','traffic_violation','other',
                'housing_allowance','transport_allowance','food_allowance','other_benefit'
            ) NOT NULL");
    }
};
