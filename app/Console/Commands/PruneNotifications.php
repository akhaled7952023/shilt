<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * P4-011 — Weekly cleanup of the notifications table.
 * Deletes read portal notifications older than --days (default: 90 days).
 * Unread notifications are preserved regardless of age.
 */
class PruneNotifications extends Command
{
    protected $signature   = 'notifications:prune {--days=90 : Delete read notifications older than this many days}';
    protected $description = 'Prune old read portal notifications from the notifications table';

    public function handle(): int
    {
        $days   = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days)->toDateTimeString();

        $count = DB::table('notifications')
            ->where('channel', 'portal')
            ->whereNotNull('read_at')           // never delete unread
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$count} read notifications older than {$days} days.");
        return Command::SUCCESS;
    }
}
