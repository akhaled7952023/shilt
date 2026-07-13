<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->unique()
                ->constrained('support_tickets')
                ->comment('One-to-one with support_tickets');
            $table->foreignId('delegate_id')
                ->constrained('delegates');

            $table->enum('request_category', [
                'advance_request',
                'fuel_request',
                'traffic_violation_request',
                'penalty_request',
                'other_financial_request',
            ]);

            // Delegate-provided at ticket creation
            $table->decimal('requested_amount', 10, 2)->nullable()
                ->comment('The amount the delegate states they need; informational only');
            $table->text('requested_notes')->nullable()
                ->comment('Delegate explanation');

            // Review outcome
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable()
                ->comment('Required when status = rejected');

            // Populated only on approval
            $table->string('approved_deduction_type', 50)->nullable()
                ->comment('Maps to HungerStationFtrDelegateDeduction type constants');
            $table->tinyInteger('approved_is_benefit')->nullable()
                ->comment('1 = benefit added to net; 0 = deduction removed from net');
            $table->string('approved_label', 200)->nullable();
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->foreignId('approved_monthly_period_id')->nullable()
                ->constrained('monthly_periods')
                ->comment('The period this approved request will be applied to');
            $table->text('approved_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('delegate_id', 'idx_fr_delegate');
            $table->index('status',      'idx_fr_status');
            $table->index(['approved_monthly_period_id', 'status'], 'idx_fr_period_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_requests');
    }
};
