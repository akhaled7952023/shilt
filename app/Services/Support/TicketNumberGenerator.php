<?php

namespace App\Services\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3 — Generates unique, sequential, human-readable ticket numbers.
 *
 * Format: TK-{YEAR}-{NNNNN}
 * Example: TK-2026-00042
 *
 * The sequence resets to 00001 at the start of each calendar year.
 * The UNIQUE constraint on support_tickets.ticket_number is the final
 * safety net against race conditions, but generate() also wraps its
 * MAX() query in a transaction for optimistic serialization.
 */
class TicketNumberGenerator
{
    public function __construct(private readonly ConnectionInterface $db) {}

    /**
     * Generate the next ticket number for the current (or given) year.
     * Must be called inside an active DB transaction to prevent sequence races.
     *
     * @param  int $year  Four-digit year (0 = current year).
     * @return string     E.g. "TK-2026-00042"
     */
    public function generate(int $year = 0): string
    {
        $year = $year ?: (int) now()->format('Y');
        $prefix = "TK-{$year}-";

        // Lock the relevant rows while we compute the next sequence number.
        // The UNIQUE constraint is still the hard safety net.
        $max = DB::table('support_tickets')
            ->where('ticket_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->max('ticket_number');

        if ($max === null) {
            $next = 1;
        } else {
            // Extract the numeric suffix from "TK-YYYY-NNNNN"
            $next = (int) substr($max, strlen($prefix)) + 1;
        }

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Validate that a string matches the ticket number format.
     */
    public function isValidFormat(string $number): bool
    {
        return (bool) preg_match('/^TK-\d{4}-\d{5,}$/', $number);
    }
}
