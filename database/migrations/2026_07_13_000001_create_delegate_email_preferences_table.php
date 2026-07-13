<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegate_email_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->unique()->constrained('delegates')->cascadeOnDelete();

            // One column per preference category — all default to enabled (opt-out model)
            $table->boolean('settlement_published')->default(true);
            $table->boolean('ticket_reply')->default(true);
            $table->boolean('financial_updates')->default(true);
            $table->boolean('expiry_reminders')->default(true);
            $table->boolean('announcements')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegate_email_preferences');
    }
};
