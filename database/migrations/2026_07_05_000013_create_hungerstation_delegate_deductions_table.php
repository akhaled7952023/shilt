<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_delegate_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained('hungerstation_delegate_settlements');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->enum('deduction_type', [
                'fuel', 'wallet', 'advance', 'app_penalty', 'company_penalty',
                'previous_balance', 'distance_deduction', 'traffic_violation', 'other',
            ]);
            $table->string('label', 200)->nullable();
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('settlement_id');
            $table->index(['monthly_period_id', 'delegate_id'], 'hs_deductions_period_delegate_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_delegate_deductions');
    }
};
