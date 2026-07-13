<?php

namespace App\Models;

use App\Enums\FinancialRequestStatus;
use App\Enums\TicketCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Phase 3 — Financial request attached to a financial-category ticket.
 * Created automatically on ticket submission for financial categories.
 * One row per ticket (unique on ticket_id).
 *
 * @property int                      $id
 * @property int                      $ticket_id
 * @property int                      $delegate_id
 * @property string                   $request_category
 * @property float|null               $requested_amount
 * @property string|null              $requested_notes
 * @property FinancialRequestStatus   $status
 * @property int|null                 $reviewed_by
 * @property \Carbon\Carbon|null      $reviewed_at
 * @property string|null              $rejection_reason
 * @property string|null              $approved_deduction_type
 * @property int|null                 $approved_is_benefit
 * @property string|null              $approved_label
 * @property float|null               $approved_amount
 * @property int|null                 $approved_monthly_period_id
 * @property string|null              $approved_notes
 * @property int|null                 $settlement_month  1–12; populated on approval
 * @property int|null                 $settlement_year   e.g. 2026; populated on approval
 */
class FinancialRequest extends Model
{
    protected $table = 'financial_requests';

    protected $fillable = [
        'ticket_id',
        'delegate_id',
        'request_category',
        'requested_amount',
        'requested_notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'approved_deduction_type',
        'approved_is_benefit',
        'approved_label',
        'approved_amount',
        'approved_monthly_period_id',
        'approved_notes',
        'settlement_month',
        'settlement_year',
    ];

    protected $casts = [
        'status'              => FinancialRequestStatus::class,
        'request_category'    => TicketCategory::class,
        'requested_amount'    => 'decimal:2',
        'approved_is_benefit' => 'boolean',
        'approved_amount'     => 'decimal:2',
        'reviewed_at'         => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedPeriod(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'approved_monthly_period_id');
    }

    public function pendingEntry(): HasOne
    {
        return $this->hasOne(PendingFinancialEntry::class, 'financial_request_id');
    }

    // ── Business Methods (P2-002) ─────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->status === FinancialRequestStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === FinancialRequestStatus::Rejected;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            FinancialRequestStatus::Pending,
            FinancialRequestStatus::NeedsInfo,
        ]);
    }

    /**
     * Approve is allowed when the request is pending (or needs_info) and either
     * no entry exists yet, or the existing entry was cancelled (re-approval after cancellation).
     */
    public function canBeApproved(): bool
    {
        if (! $this->isPending()) {
            // Already approved — allow only if existing entry was cancelled
            if ($this->isApproved()) {
                $entry = $this->pendingEntry;
                return $entry !== null && $entry->isCancelled();
            }
            return false;
        }
        return true;
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }
}
