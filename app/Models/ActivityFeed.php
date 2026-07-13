<?php

namespace App\Models;

use App\Enums\ActivityActorType;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 3 — Global admin activity feed entry.
 * Append-only: rows are never updated or deleted.
 * Distinct from TicketAuditLog (which is scoped to a single ticket).
 * Admin-facing only; never exposed to the delegate portal.
 *
 * @property int               $id
 * @property string            $platform
 * @property ActivityActorType $actor_type
 * @property int|null          $actor_id       delegate_id or user_id; NULL for system events
 * @property string            $actor_label    Cached display name — permanent readability
 * @property string            $action         Slug: ticket_created, request_approved, entry_imported, etc.
 * @property string|null       $subject_type   Model class, e.g. App\Models\SupportTicket
 * @property int|null          $subject_id
 * @property string|null       $subject_label  Cached subject identifier, e.g. TK-2026-00042
 * @property string            $description    Pre-rendered English description
 * @property array|null        $data
 * @property \Carbon\Carbon    $created_at
 */
class ActivityFeed extends Model
{
    protected $table = 'activity_feed';

    public $timestamps = false;

    protected $fillable = [
        'platform',
        'actor_type',
        'actor_id',
        'actor_label',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'data',
    ];

    protected $casts = [
        'actor_type' => ActivityActorType::class,
        'data'       => 'array',
        'created_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForPlatform($query, string $platform = 'hungerstation')
    {
        return $query->where('platform', $platform);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
