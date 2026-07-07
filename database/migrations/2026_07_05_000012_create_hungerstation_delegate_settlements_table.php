<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_delegate_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->foreignId('import_batch_id')->constrained('hungerstation_import_batches');
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('gross_delivery_fees', 12, 2)->default(0);
            $table->decimal('platform_deductions', 12, 2)->default(0);
            $table->decimal('platform_compensations', 12, 2)->default(0);
            $table->decimal('cash_collected', 12, 2)->default(0);
            $table->decimal('housing_allowance', 10, 2)->default(0);
            $table->decimal('entitlements_manual', 10, 2)->default(0);
            $table->decimal('grants_manual', 10, 2)->default(0);
            $table->decimal('commission_total', 12, 2)->default(0);
            $table->decimal('deductions_total', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['monthly_period_id', 'delegate_id'], 'hs_settlements_period_delegate_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_delegate_settlements');
    }
};
