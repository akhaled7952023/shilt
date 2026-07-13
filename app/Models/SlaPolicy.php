<?php

namespace App\Models;

use App\Enums\TicketPriority;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 3 — SLA policy lookup table.
 * Always exactly 4 rows (one per priority level).
 * Seeded at migration time. Editable by super_admin via System Settings.
 * Changes only affect future tickets; existing deadlines are not recalculated.
 *
 * @property int           $id
 * @property TicketPriority $priority
 * @property int           $first_response_hours  Hours until first admin reply must arrive
 * @property int           $resolution_hours      Hours until ticket must be resolved
 */
class SlaPolicy extends Model
{
    protected $table = 'sla_policies';

    protected $fillable = [
        'priority',
        'first_response_hours',
        'resolution_hours',
    ];

    protected $casts = [
        'priority'             => TicketPriority::class,
        'first_response_hours' => 'integer',
        'resolution_hours'     => 'integer',
    ];

    /** Retrieve the policy for a given priority (cached in-memory per request). */
    public static function forPriority(TicketPriority $priority): ?self
    {
        return static::where('priority', $priority->value)->first();
    }
}
