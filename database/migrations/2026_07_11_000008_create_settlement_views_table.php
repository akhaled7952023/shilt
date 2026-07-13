<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_views', function (Blueprint $table) {
            $table->id();

            $table->string('platform', 20)->default('hungerstation');

            // Plain integer reference — no FK constraint to avoid coupling to the
            // platform-specific settlement table (which may not exist in all environments).
            $table->unsignedBigInteger('settlement_id')
                ->comment('References hungerstation_ftr_settlements.id');
            $table->foreignId('delegate_id')->constrained('delegates');
            $table->foreignId('monthly_period_id')->constrained('monthly_periods')
                ->comment('Denormalised for query convenience');

            $table->timestamp('first_viewed_at')->useCurrent();
            $table->timestamp('last_viewed_at')->useCurrent();
            $table->unsignedInteger('view_count')->default(1);

            $table->tinyInteger('notification_sent')->default(0)
                ->comment('1 once the admin notification has been sent; prevents duplicates');

            // One row per settlement per delegate
            $table->unique(['settlement_id', 'delegate_id'], 'uq_sv_settlement_delegate');

            // Indexes
            $table->index('monthly_period_id', 'idx_sv_period');
            $table->index(['monthly_period_id', 'notification_sent'], 'idx_sv_notification_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_views');
    }
};
