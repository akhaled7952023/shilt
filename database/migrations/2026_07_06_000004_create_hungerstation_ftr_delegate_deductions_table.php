<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_ftr_delegate_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')
                ->constrained('hungerstation_ftr_settlements')
                ->onDelete('cascade');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->enum('deduction_type', [
                'fuel', 'iqama', 'advance', 'vehicle',
                'app_penalty', 'company_penalty', 'traffic_violation', 'other',
            ]);
            $table->string('label', 200);
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['settlement_id'], 'ftr_deductions_settlement_idx');
            $table->index(['monthly_period_id', 'delegate_id'], 'ftr_deductions_period_delegate_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_ftr_delegate_deductions');
    }
};
