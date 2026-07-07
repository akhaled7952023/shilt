<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('hungerstation_import_batches');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->foreignId('delegate_id')->nullable()->constrained('delegates');
            $table->string('raw_driver_id', 20);
            $table->string('raw_driver_name', 200);
            $table->date('order_date')->nullable();
            $table->string('order_id_platform', 50);
            $table->string('team_name', 200)->nullable();
            $table->string('head_name', 100)->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('company_commission', 10, 2)->default(0);
            $table->decimal('deduction_amount', 10, 2)->default(0);
            $table->text('deduction_note')->nullable();
            $table->decimal('compensation', 10, 2)->default(0);
            $table->text('compensation_note')->nullable();
            $table->decimal('cash_collected', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['monthly_period_id', 'order_id_platform'], 'hs_orders_period_order_uq');
            $table->index(['monthly_period_id', 'delegate_id'], 'hs_orders_period_delegate_idx');
            $table->index('import_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_orders');
    }
};
