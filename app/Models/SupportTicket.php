<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Phase 3 — Support Ticket root model.
 *
 * @property int                  $id
 * @property string               $ticket_number   TK-YYYY-NNNNN
 * @property string               $platform
 * @property TicketSource         $source
 * @property int                  $delegate_id
 * @property int|null             $assigned_to
 * @property TicketCategory       $category
 * @property TicketPriority       $priority
 * @property string               $subject
 * @property int|null             $related_monthly_period_id
 * @property TicketStatus         $status
 * @property \Carbon\Carbon|null  $close_grace_deadline
 * @property \Carbon\Carbon|null  $permanently_closed_at
 * @property \Carbon\Carbon|null  $sla_first_response_deadline
 * @property \Carbon\Carbon|null  $sla_resolution_deadline
 * @property int|null             $sla_first_response_met
 * @property int|null             $sla_resolution_met
 * @property \Carbon\Carbon       $opened_at
 * @property \Carbon\Carbon|null  $first_reply_at
 * @property \Carbon\Carbon|null  $resolved_at
 * @property \Carbon\Carbon|null  $closed_at
 * @property \Carbon\Carbon       $last_activity_at
 * @property int                  $created_by
 * @property int|null             $updated_by
 */
class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'ticket_number',
        'platform',
        'source',
        'delegate_id',
        'assigned_to',
        'category',
        'priority',
        'subject',
        'related_monthly_period_id',
        'status',
        'close_grace_deadline',
        'permanently_closed_at',
        'sla_first_response_deadline',
        'sla_resolution_deadline',
        'sla_first_response_met',
        'sla_resolution_met',
        'opened_at',
        'first_reply_at',
        'resolved_at',
        'closed_at',
        'last_activity_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'source'                       => TicketSource::class,
        'category'                     => TicketCategory::class,
        'priority'                     => TicketPriority::class,
        'status'                       => TicketStatus::class,
        'close_grace_deadline'         => 'datetime',
        'permanently_closed_at'        => 'datetime',
        'sla_first_response_deadline'  => 'datetime',
        'sla_resolution_deadline'      => 'datetime',
        'opened_at'                    => 'datetime',
        'first_reply_at'               => 'datetime',
        'resolved_at'                  => 'datetime',
        'closed_at'                    => 'datetime',
        'last_activity_at'             => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function relatedPeriod(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'related_monthly_period_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'ticket_id');
    }

    public function financialRequest(): HasOne
    {
        return $this->hasOne(FinancialRequest::class, 'ticket_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(TicketAuditLog::class, 'ticket_id')->orderBy('created_at');
    }

    // ── Business Methods ──────────────────────────────────────────────────────

    /** True once permanently_closed_at is set (grace period has expired or forced). */
    public function isClosedPermanently(): bool
    {
        return $this->permanently_closed_at !== null;
    }

    /** True if status is resolved and the grace period deadline has not yet passed. */
    public function isWithinGracePeriod(): bool
    {
        return $this->status === TicketStatus::Resolved
            && $this->close_grace_deadline !== null
            && now()->lt($this->close_grace_deadline);
    }

    /** Delegate may reply when status allows it and the ticket is not permanently closed. */
    public function canDelegateReply(): bool
    {
        if ($this->isClosedPermanently()) {
            return false;
        }

        return $this->status?->allowsDelegateReply() ?? false;
    }

    /** Admins may reply on any ticket that is not permanently closed. */
    public function canAdminReply(): bool
    {
        return ! $this->isClosedPermanently();
    }

    /**
     * Transition the ticket to resolved, calculate the grace deadline, and
     * evaluate whether the resolution SLA was met.
     * Caller must save() after this call.
     *
     * @param  \App\Models\User $admin
     */
    public function markResolved(\App\Models\User $admin): void
    {
        $now = now();

        $graceHours = (int) (SystemSetting::get('support_auto_close_resolved_days') * 24
            ?: (int) (SystemSetting::get('ticket_close_grace_hours') ?? 72));

        $this->status               = TicketStatus::Resolved;
        $this->resolved_at          = $now;
        $this->close_grace_deadline = $now->copy()->addHours($graceHours);
        $this->last_activity_at     = $now;
        $this->updated_by           = $admin->id;

        if ($this->sla_resolution_deadline) {
            $this->sla_resolution_met = $now->lte($this->sla_resolution_deadline) ? 1 : 0;
        }
    }

    /**
     * Permanently close the ticket (called by grace-period expiry job or force-close).
     * Caller must save() after this call.
     */
    public function markClosed(): void
    {
        $now = now();

        $this->status               = TicketStatus::Closed;
        $this->closed_at            = $now;
        $this->permanently_closed_at = $now;
        $this->last_activity_at     = $now;
    }

    /**
     * Assign the ticket to an admin. If status is Open, moves it to InProgress.
     * Caller must save() after this call.
     *
     * @param  \App\Models\User $admin
     */
    public function assignTo(\App\Models\User $admin): void
    {
        $this->assigned_to      = $admin->id;
        $this->last_activity_at = now();

        if ($this->status === TicketStatus::Open) {
            $this->status = TicketStatus::InProgress;
        }
    }

    // ── Query Scopes ──────────────────────────────────────────────────────────

    /** Active (non-closed) tickets. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TicketStatus::Open->value,
            TicketStatus::InProgress->value,
            TicketStatus::AwaitingDelegate->value,
            TicketStatus::Reopened->value,
        ]);
    }

    public function scopeForDelegate(Builder $query, int $delegateId): Builder
    {
        return $query->where('delegate_id', $delegateId);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeByCategory(Builder $query, TicketCategory|string $category): Builder
    {
        $value = $category instanceof TicketCategory ? $category->value : $category;
        return $query->where('category', $value);
    }

    public function scopeBySource(Builder $query, TicketSource|string $source): Builder
    {
        $value = $source instanceof TicketSource ? $source->value : $source;
        return $query->where('source', $value);
    }

    /** Tickets whose first-response deadline has passed with no admin reply yet. */
    public function scopeOverdueFirstResponse(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', [TicketStatus::Closed->value, TicketStatus::Resolved->value])
            ->whereNotNull('sla_first_response_deadline')
            ->where('sla_first_response_deadline', '<', now())
            ->whereNull('first_reply_at');
    }

    /** Tickets whose resolution deadline has passed and are still not resolved. */
    public function scopeOverdueResolution(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', [TicketStatus::Closed->value, TicketStatus::Resolved->value])
            ->whereNotNull('sla_resolution_deadline')
            ->where('sla_resolution_deadline', '<', now())
            ->whereNull('resolved_at');
    }
}
