<?php

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Services\Support\TicketLifecycleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3 — Closes resolved tickets whose grace period has expired.
 *
 * Runs every hour (registered in routes/console.php).
 * Idempotent: safe to run multiple times for the same time window.
 */
class ExpireTicketGracePeriods extends Command
{
    protected $signature   = 'tickets:expire-grace-periods';
    protected $description = 'Close resolved support tickets whose grace period deadline has passed';

    public function __construct(private readonly TicketLifecycleService $lifecycleService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tickets = SupportTicket::where('status', TicketStatus::Resolved->value)
            ->whereNotNull('close_grace_deadline')
            ->where('close_grace_deadline', '<=', now())
            ->get();

        if ($tickets->isEmpty()) {
            $this->info('No expired grace periods found.');
            return self::SUCCESS;
        }

        $closed = 0;

        foreach ($tickets as $ticket) {
            try {
                $this->lifecycleService->expireGracePeriod($ticket);
                $closed++;
            } catch (\Throwable $e) {
                Log::error('ExpireTicketGracePeriods failed for ticket', [
                    'ticket_id'     => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'error'         => $e->getMessage(),
                ]);
                $this->error("Failed to close ticket {$ticket->ticket_number}: {$e->getMessage()}");
            }
        }

        $this->info("Closed {$closed} ticket(s) after grace period expiry.");

        return self::SUCCESS;
    }
}
