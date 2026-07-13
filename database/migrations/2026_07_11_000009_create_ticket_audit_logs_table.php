<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('support_tickets')
                ->cascadeOnDelete();

            // Actor
            $table->enum('actor_type', ['delegate', 'admin', 'system']);
            $table->unsignedBigInteger('actor_id')->nullable()
                ->comment('delegate_id or user_id; NULL for system-generated events');
            $table->string('actor_label', 150)
                ->comment('Cached display name at write time for permanent readability');

            // Event
            $table->string('action', 80)
                ->comment('E.g. ticket_opened, assigned, status_changed, replied, priority_changed');

            // Before/after values for status and other transitions
            $table->string('from_value', 100)->nullable()
                ->comment('Previous value (e.g. old status)');
            $table->string('to_value', 100)->nullable()
                ->comment('New value (e.g. new status)');

            // Human-readable description
            $table->string('description', 500);

            // Optional structured data
            $table->json('data')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('ticket_id',                   'idx_tal_ticket');
            $table->index(['ticket_id', 'created_at'],   'idx_tal_ticket_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_audit_logs');
    }
};
