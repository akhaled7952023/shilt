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
        Schema::create('company_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_monthly_entry_id')->constrained('delegate_monthly_entries')->cascadeOnDelete();
            $table->foreignId('deduction_category_id')->nullable()->constrained('deduction_categories')->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('delegate_monthly_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_deductions');
    }
};
