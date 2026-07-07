<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_ftr_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->foreignId('import_batch_id')->constrained('hungerstation_ftr_import_batches');
            $table->string('rider_id_platform', 20)->comment('HS Rider ID from RLVL sheet');

            // Source data — stored verbatim from RLVL sheet (penalties stored as positive abs values)
            $table->unsignedInteger('total_orders')->default(0);          // col C
            $table->decimal('basic_payment', 12, 2)->default(0);          // col E — company revenue
            $table->decimal('acceptance_rate_penalties', 10, 2)->default(0); // col F
            $table->decimal('contact_rate_penalties', 10, 2)->default(0);    // col G
            $table->decimal('stacking_deduction', 10, 2)->default(0);        // col H — stored only, NOT in formula
            $table->decimal('declined_penalties', 10, 2)->default(0);        // col I
            $table->decimal('late_penalty', 10, 2)->default(0);              // col J
            $table->decimal('no_show_penalty', 10, 2)->default(0);           // col K
            $table->decimal('no_show_penalty_special_cities', 10, 2)->default(0); // col L
            $table->decimal('daily_acceptance_rate_penalty', 10, 2)->default(0);  // col M
            $table->decimal('distance_payment', 12, 2)->default(0);          // col N — delegate gross base
            $table->decimal('missed_days_penalty', 10, 2)->default(0);       // col O

            // Informational columns (zero in May 2026 — stored for future use)
            $table->decimal('city_payment', 10, 2)->default(0);             // col D
            $table->decimal('segment_payment', 10, 2)->default(0);          // col P
            $table->decimal('courier_basic_payment', 10, 2)->default(0);    // col Q
            $table->decimal('courier_scoring_payment', 10, 2)->default(0);  // col R

            $table->decimal('rider_balance', 10, 2)->default(0);            // col S — wallet already received

            // Computed: SUM of all 8 platform penalty columns (F+G+I+J+K+L+M+O)
            $table->decimal('total_platform_penalties', 12, 2)->default(0);

            // Admin-entered post-import adjustments
            $table->decimal('housing_allowance', 10, 2)->default(0);
            $table->decimal('company_deductions_total', 12, 2)->default(0); // sum of ftr_delegate_deductions

            // Final computed net salary
            $table->decimal('net_salary', 12, 2)->default(0);

            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['monthly_period_id', 'delegate_id'], 'ftr_settlement_period_delegate_uq');
            $table->index(['monthly_period_id'], 'ftr_settlement_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_ftr_settlements');
    }
};
