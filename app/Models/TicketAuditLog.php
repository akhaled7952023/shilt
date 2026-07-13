<?php

namespace App\Models;

use App\Enums\ActivityActorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 3 — Per-ticket audit timeline entry.
 * Append-only: rows are never updated or deleted.
 * Distinct from ActivityFeed (which is a global admin stream).
 *
 * @property int               $id
 * @property int               $ticket_id
 * @property ActivityActorType $actor_type
 * @property int|null          $actor_id       delegate_id or user_id; NULL for system events
 * @property string            $actor_label    Cached display name at write time
 * @property string            $action         E.g. ticket_opened, assigned, status_changed
 * @property string|null       $from_value     Previous value for transitions
 * @property string|null       $to_value       New value for transitions
 * @property string            $description
 * @property array|null        $data
 * @property \Carbon\Carbon    $created_at
 */
class TicketAuditLog extends Model
{
    protected $table = 'ticket_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'actor_type',
        'actor_id',
        'actor_label',
        'action',
        'from_value',
        'to_value',
        'description',
        'data',
    ];

    protected $casts = [
        'actor_type' => ActivityActorType::class,
        'data'       => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
