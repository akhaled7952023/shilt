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
        Schema::create('warning_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->onDelete('restrict');
            $table->foreignId('monthly_period_id')->nullable()->constrained('monthly_periods')->onDelete('restrict');
            $table->foreignId('warning_type_id')->nullable()->constrained('warning_types')->onDelete('restrict');
            $table->text('description');
            $table->date('warning_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('delegate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warning_entries');
    }
};
