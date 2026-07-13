<?php

use App\Console\Commands\ExpireTicketGracePeriods;
use App\Console\Commands\PruneNotifications;
use App\Console\Commands\SendExpiryAlerts;
use App\Console\Commands\SendTestEmail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 3 — Close resolved tickets after grace period expires
Schedule::command(ExpireTicketGracePeriods::class)->hourly();

// Batch 6 — Daily expiry alerts for delegate/vehicle documents
Schedule::command(SendExpiryAlerts::class)->dailyAt('08:00');

// Batch 6 — Weekly cleanup of old read notifications
Schedule::command(PruneNotifications::class)->weekly();

// SendTestEmail is not scheduled — run manually: php artisan mail:test --to=you@example.com
