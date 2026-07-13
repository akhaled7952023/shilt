<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 3 — Settlement viewed-at tracking.
 * One row per (settlement_id, delegate_id) pair — never duplicated.
 * Tracks the first and most recent time a delegate opened their monthly salary statement.
 * HungerStation only.
 *
 * @property int                  $id
 * @property string               $platform
 * @property int                  $settlement_id        References hungerstation_ftr_settlements.id
 * @property int                  $delegate_id
 * @property int                  $monthly_period_id
 * @property \Carbon\Carbon       $first_viewed_at
 * @property \Carbon\Carbon       $last_viewed_at
 * @property int                  $view_count
 * @property bool                 $notification_sent    True once admin notification was dispatched
 */
class SettlementView extends Model
{
    protected $table = 'settlement_views';

    public $timestamps = false;

    protected $fillable = [
        'platform',
        'settlement_id',
        'delegate_id',
        'monthly_period_id',
        'first_viewed_at',
        'last_viewed_at',
        'view_count',
        'notification_sent',
    ];

    protected $casts = [
        'first_viewed_at'   => 'datetime',
        'last_viewed_at'    => 'datetime',
        'notification_sent' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }
}
