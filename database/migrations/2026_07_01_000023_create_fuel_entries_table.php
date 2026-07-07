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
        Schema::create('fuel_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->onDelete('restrict');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods')->onDelete('restrict');
            $table->decimal('amount_sar', 10, 2);
            $table->date('entry_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('delegate_id');
            $table->index('monthly_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_entries');
    }
};
