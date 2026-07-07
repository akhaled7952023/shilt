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
        Schema::create('leave_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->onDelete('restrict');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods')->onDelete('restrict');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days_count');
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
        Schema::dropIfExists('leave_entries');
    }
};
