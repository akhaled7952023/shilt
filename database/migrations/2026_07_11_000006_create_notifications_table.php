<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 unified notification store.
 * Distinct from the existing `delegate_notifications` table (simple portal notifications).
 * This table handles all channels (portal, email, sms, whatsapp) for both delegates and admins.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Recipient
            $table->enum('recipient_type', ['delegate', 'admin']);
            $table->unsignedBigInteger('recipient_id')
                ->comment('delegate_id or user_id depending on recipient_type');

            // Delivery channel
            $table->enum('channel', ['portal', 'email', 'sms', 'whatsapp'])->default('portal');

            // Classification
            $table->enum('category', [
                'settlement_published',
                'settlement_viewed',
                'ticket_new',
                'ticket_reply',
                'ticket_closed',
                'ticket_reopened',
                'financial_request_approved',
                'financial_request_rejected',
                'iqama_expiring',
                'passport_expiring',
                'driving_license_expiring',
                'vehicle_registration_expiring',
                'vehicle_insurance_expiring',
            ]);

            // Content
            $table->string('title', 255);
            $table->text('body');
            $table->string('action_url', 500)->nullable()
                ->comment('Deep link to the relevant page');

            // Delivery status
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable()
                ->comment('Populated when email/SMS/WA actually dispatched');
            $table->timestamp('failed_at')->nullable()
                ->comment('Populated if delivery failed after retries');

            // Polymorphic subject (what generated this notification)
            $table->string('notifiable_type', 100)->nullable()
                ->comment('e.g. App\\Models\\SupportTicket');
            $table->unsignedBigInteger('notifiable_id')->nullable();

            // Extra payload
            $table->json('data')->nullable()
                ->comment('Arbitrary context: ticket_id, period_id, delegate_name, etc.');

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['recipient_type', 'recipient_id'],           'idx_notif_recipient');
            $table->index(['recipient_type', 'recipient_id', 'read_at'],'idx_notif_recipient_unread');
            $table->index('channel',                                    'idx_notif_channel');
            $table->index('category',                                   'idx_notif_category');
            $table->index(['notifiable_type', 'notifiable_id'],         'idx_notif_notifiable');
            $table->index('created_at',                                 'idx_notif_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
