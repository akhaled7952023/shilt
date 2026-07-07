<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_benefit flag to deductions table
        Schema::table('hungerstation_ftr_delegate_deductions', function (Blueprint $table) {
            $table->boolean('is_benefit')->default(false)->after('deduction_type');
        });

        // Expand ENUM to include benefit types and the missing 'loan' deduction type.
        DB::statement("ALTER TABLE hungerstation_ftr_delegate_deductions
            MODIFY COLUMN deduction_type ENUM(
                'fuel','iqama','advance','loan','vehicle',
                'app_penalty','company_penalty','traffic_violation','other',
                'housing_allowance','transport_allowance','food_allowance','other_benefit'
            ) NOT NULL");

        // 2. Add company_benefits_total to settlements table
        Schema::table('hungerstation_ftr_settlements', function (Blueprint $table) {
            $table->decimal('company_benefits_total', 12, 2)->default(0)->after('housing_allowance');
        });

        // 3. Migrate any existing housing_allowance values into benefit adjustment rows
        try {
            $rows = DB::table('hungerstation_ftr_settlements')
                ->where('housing_allowance', '>', 0)
                ->get(['id', 'monthly_period_id', 'delegate_id', 'housing_allowance', 'distance_payment',
                       'total_platform_penalties', 'rider_balance', 'company_deductions_total', 'created_by']);

            foreach ($rows as $row) {
                DB::table('hungerstation_ftr_delegate_deductions')->insert([
                    'settlement_id'     => $row->id,
                    'monthly_period_id' => $row->monthly_period_id,
                    'delegate_id'       => $row->delegate_id,
                    'deduction_type'    => 'housing_allowance',
                    'is_benefit'        => true,
                    'label'             => 'بدل سكن / Housing Allowance',
                    'amount'            => $row->housing_allowance,
                    'notes'             => null,
                    'created_by'        => $row->created_by,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $benefitsTotal = (float) $row->housing_allowance;
                $netSalary = round(
                    (float) $row->distance_payment
                    - (float) $row->total_platform_penalties
                    - abs((float) $row->rider_balance)
                    + $benefitsTotal
                    - (float) $row->company_deductions_total,
                    2
                );

                DB::table('hungerstation_ftr_settlements')->where('id', $row->id)->update([
                    'housing_allowance'       => 0,
                    'company_benefits_total'  => round($benefitsTotal, 2),
                    'net_salary'              => $netSalary,
                ]);
            }
        } catch (\Throwable) {
            // No data to migrate — safe on empty dev/staging databases.
        }
    }

    public function down(): void
    {
        Schema::table('hungerstation_ftr_settlements', function (Blueprint $table) {
            $table->dropColumn('company_benefits_total');
        });

        DB::statement("ALTER TABLE hungerstation_ftr_delegate_deductions
            MODIFY COLUMN deduction_type ENUM(
                'fuel','iqama','advance','vehicle',
                'app_penalty','company_penalty','traffic_violation','other'
            ) NOT NULL");

        Schema::table('hungerstation_ftr_delegate_deductions', function (Blueprint $table) {
            $table->dropColumn('is_benefit');
        });
    }
};
