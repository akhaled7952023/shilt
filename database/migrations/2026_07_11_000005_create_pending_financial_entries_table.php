<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_financial_entries', function (Blueprint $table) {
            $table->id();

            $table->string('platform', 20)->default('hungerstation');
            $table->enum('source_type', ['ticket', 'manual'])->default('ticket')
                ->comment('ticket = from approval flow; manual = admin created directly (future)');

            $table->foreignId('financial_request_id')->nullable()->unique()
                ->constrained('financial_requests')
                ->comment('NULL if source_type = manual');

            // Target
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');

            // Adjustment details (mirrors hungerstation_ftr_delegate_deductions)
            $table->string('deduction_type', 50);
            $table->tinyInteger('is_benefit')->default(0);
            $table->string('label', 200);
            $table->decimal('amount', 10, 2);
            $table->text('notes')->nullable();

            // Lifecycle
            $table->enum('status', ['pending', 'imported', 'cancelled'])->default('pending');

            // Creation
            $table->foreignId('created_by')->constrained('users')
                ->comment('Admin who approved the financial request');

            $table->timestamps();

            // Import tracking — populated when status moves to imported
            $table->timestamp('imported_at')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users');

            // References to platform tables — stored as plain integers (no FK constraint)
            // to avoid coupling Phase 3 to the settlement table structure.
            $table->unsignedBigInteger('settlement_id')->nullable()
                ->comment('References hungerstation_ftr_settlements.id after import');
            $table->unsignedBigInteger('adjustment_id')->nullable()
                ->comment('References hungerstation_ftr_delegate_deductions.id after import');

            // Indexes
            $table->index(['platform', 'monthly_period_id', 'status'], 'idx_pfe_platform_period_status');
            $table->index(['delegate_id', 'monthly_period_id'],        'idx_pfe_delegate_period');
            $table->index('status',                                    'idx_pfe_status');
            $table->index('settlement_id',                             'idx_pfe_settlement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_financial_entries');
    }
};
