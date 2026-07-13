<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_feed', function (Blueprint $table) {
            $table->id();

            $table->string('platform', 20)->default('hungerstation');

            // Actor
            $table->enum('actor_type', ['delegate', 'admin', 'system']);
            $table->unsignedBigInteger('actor_id')->nullable()
                ->comment('delegate_id or user_id; NULL for system events');
            $table->string('actor_label', 150)
                ->comment('Cached display name — stored for audit stability; readable even if record is later renamed or soft-deleted');

            // Event
            $table->string('action', 80)
                ->comment('Slug identifying the event type, e.g. ticket_created, request_approved, entry_imported');

            // Subject (what the action was performed on)
            $table->string('subject_type', 100)->nullable()
                ->comment('Model class, e.g. App\\Models\\SupportTicket');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label', 255)->nullable()
                ->comment('Cached subject identifier, e.g. TK-2026-00042');

            // Human-readable description — stored pre-rendered for permanent readability
            $table->string('description', 500);

            // Extra context for future detail panels
            $table->json('data')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['platform', 'created_at'], 'idx_af_platform_created');
            $table->index(['actor_type', 'actor_id'], 'idx_af_actor');
            $table->index(['subject_type', 'subject_id'], 'idx_af_subject');
            $table->index('action', 'idx_af_action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_feed');
    }
};
