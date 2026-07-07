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
        Schema::create('vehicle_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->foreignId('delegate_id')->nullable()->constrained('delegates')->onDelete('restrict');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods')->onDelete('restrict');
            $table->enum('payment_by', ['company', 'delegate']);
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('monthly_period_id');
            $table->index('vehicle_id');
            $table->index('payment_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_rentals');
    }
};
