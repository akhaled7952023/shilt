<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('ticket_number', 20)->unique()
                ->comment('Human-readable ID: TK-YYYY-NNNNN, e.g. TK-2026-00042');
            $table->string('platform', 20)->default('hungerstation')
                ->comment('Always hungerstation in Phase 3');
            $table->enum('source', ['portal', 'dashboard', 'system'])->default('portal')
                ->comment('How the ticket was created');

            // Parties
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Classification
            $table->enum('category', [
                'settlement_objection',
                'advance_request',
                'fuel_request',
                'traffic_violation_request',
                'penalty_request',
                'other_financial_request',
                'technical_support',
                'payroll_inquiry',
                'general_inquiry',
            ])->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')
                ->comment('Always normal on creation; only admins may change it after submission');
            $table->string('subject', 255);

            // Related monthly period (optional)
            $table->foreignId('related_monthly_period_id')->nullable()
                ->constrained('monthly_periods')->nullOnDelete();

            // Status
            $table->enum('status', [
                'open',
                'in_progress',
                'awaiting_delegate',
                'resolved',
                'reopened',
                'closed',
            ])->default('open');

            // Grace period
            $table->timestamp('close_grace_deadline')->nullable()
                ->comment('Set when status moves to resolved; delegate may reopen until this time');
            $table->timestamp('permanently_closed_at')->nullable()
                ->comment('Set when grace period expires or admin force-closes');

            // SLA deadlines — calculated on ticket creation from sla_policies + priority
            $table->timestamp('sla_first_response_deadline')->nullable()
                ->comment('opened_at + sla_policies.first_response_hours for this priority');
            $table->timestamp('sla_resolution_deadline')->nullable()
                ->comment('opened_at + sla_policies.resolution_hours for this priority');
            $table->tinyInteger('sla_first_response_met')->nullable()
                ->comment('NULL = not yet due; 1 = met; 0 = missed');
            $table->tinyInteger('sla_resolution_met')->nullable()
                ->comment('NULL = open; 1 = met; 0 = missed');

            // Timestamps
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('first_reply_at')->nullable()
                ->comment('When admin first replied');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent()
                ->comment('Updated on every reply; drives queue ordering');

            // Audit
            $table->unsignedBigInteger('created_by')
                ->comment('delegate_id who opened the ticket');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('Last admin user to modify');

            $table->timestamps();

            // Composite and individual indexes
            $table->index(['platform', 'status'],       'idx_st_platform_status');
            $table->index('delegate_id',                'idx_st_delegate');
            $table->index('priority',                   'idx_st_priority');
            $table->index('source',                     'idx_st_source');
            $table->index('related_monthly_period_id',  'idx_st_period');
            $table->index(['assigned_to', 'status'],    'idx_st_assigned_status');
            $table->index('last_activity_at',           'idx_st_last_activity');
            $table->index('close_grace_deadline',       'idx_st_grace_deadline');
            $table->index('sla_first_response_deadline','idx_st_sla_response');
            $table->index('sla_resolution_deadline',    'idx_st_sla_resolution');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
