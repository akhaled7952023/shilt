<?php

namespace App\Models;

use App\Enums\PendingEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 3 — Pending financial entry.
 * Created when a financial request is approved.
 * Held here until the accountant applies it to a settlement
 * via PendingEntryImportService (Batch 5).
 *
 * @property int                  $id
 * @property string               $platform
 * @property string               $source_type  'ticket' | 'manual'
 * @property int|null             $financial_request_id
 * @property int                  $delegate_id
 * @property int|null             $monthly_period_id
 * @property int|null             $settlement_month  1–12
 * @property int|null             $settlement_year   e.g. 2026
 * @property string               $deduction_type
 * @property bool                 $is_benefit
 * @property string               $label
 * @property float                $amount
 * @property string|null          $notes
 * @property PendingEntryStatus   $status
 * @property int                  $created_by
 * @property \Carbon\Carbon|null  $imported_at
 * @property int|null             $imported_by
 * @property int|null             $settlement_id
 * @property int|null             $adjustment_id
 */
class PendingFinancialEntry extends Model
{
    protected $table = 'pending_financial_entries';

    protected $fillable = [
        'platform',
        'source_type',
        'financial_request_id',
        'delegate_id',
        'monthly_period_id',
        'settlement_month',
        'settlement_year',
        'deduction_type',
        'is_benefit',
        'label',
        'amount',
        'notes',
        'status',
        'created_by',
        'imported_at',
        'imported_by',
        'settlement_id',
        'adjustment_id',
    ];

    protected $casts = [
        'status'      => PendingEntryStatus::class,
        'is_benefit'  => 'boolean',
        'amount'      => 'decimal:2',
        'imported_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function financialRequest(): BelongsTo
    {
        return $this->belongsTo(FinancialRequest::class);
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    // ── Business Methods (P2-008) ─────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === PendingEntryStatus::Pending;
    }

    public function isImported(): bool
    {
        return $this->status === PendingEntryStatus::Imported;
    }

    public function isCancelled(): bool
    {
        return $this->status === PendingEntryStatus::Cancelled;
    }

    /** Only pending (not yet imported) entries can be cancelled. */
    public function canBeCancelled(): bool
    {
        return $this->isPending();
    }
}
