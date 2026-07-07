<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delegate_monthly_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->onDelete('restrict');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods')->onDelete('restrict');
            $table->foreignId('platform_id')->constrained('platforms')->onDelete('restrict');
            $table->unsignedInteger('orders_count')->nullable();
            $table->decimal('eligible_km', 10, 2)->nullable();
            $table->decimal('distance_pay', 10, 2)->default(0);
            $table->decimal('tips', 10, 2)->default(0);
            $table->decimal('grants', 10, 2)->default(0);
            $table->decimal('housing_allowance', 10, 2)->default(0);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('fuel_deduction', 10, 2)->default(0);
            $table->decimal('wallet_deduction', 10, 2)->default(0);
            $table->decimal('app_deduction', 10, 2)->default(0);
            $table->decimal('previous_carry_over', 10, 2)->default(0);
            $table->decimal('short_distance_penalty', 10, 2)->default(0);
            $table->decimal('gross_entitlement', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_settlement', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['delegate_id', 'monthly_period_id', 'platform_id'], 'dme_delegate_period_platform_unique');
            $table->index('monthly_period_id');
            $table->index('delegate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegate_monthly_entries');
    }
};
