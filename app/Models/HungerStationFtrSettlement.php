<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HungerStationFtrSettlement extends Model
{
    protected $table = 'hungerstation_ftr_settlements';

    protected $fillable = [
        'monthly_period_id',
        'delegate_id',
        'import_batch_id',
        'rider_id_platform',
        'total_orders',
        'basic_payment',
        'acceptance_rate_penalties',
        'contact_rate_penalties',
        'stacking_deduction',
        'declined_penalties',
        'late_penalty',
        'no_show_penalty',
        'no_show_penalty_special_cities',
        'daily_acceptance_rate_penalty',
        'distance_payment',
        'missed_days_penalty',
        'city_payment',
        'segment_payment',
        'courier_basic_payment',
        'courier_scoring_payment',
        'rider_balance',
        'total_platform_penalties',
        'housing_allowance',
        'company_benefits_total',
        'company_deductions_total',
        'net_salary',
        'is_locked',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'basic_payment'                    => 'decimal:2',
        'acceptance_rate_penalties'        => 'decimal:2',
        'contact_rate_penalties'           => 'decimal:2',
        'stacking_deduction'               => 'decimal:2',
        'declined_penalties'               => 'decimal:2',
        'late_penalty'                     => 'decimal:2',
        'no_show_penalty'                  => 'decimal:2',
        'no_show_penalty_special_cities'   => 'decimal:2',
        'daily_acceptance_rate_penalty'    => 'decimal:2',
        'distance_payment'                 => 'decimal:2',
        'missed_days_penalty'              => 'decimal:2',
        'city_payment'                     => 'decimal:2',
        'segment_payment'                  => 'decimal:2',
        'courier_basic_payment'            => 'decimal:2',
        'courier_scoring_payment'          => 'decimal:2',
        'rider_balance'                    => 'decimal:2',
        'total_platform_penalties'         => 'decimal:2',
        'housing_allowance'                => 'decimal:2',
        'company_benefits_total'           => 'decimal:2',
        'company_deductions_total'         => 'decimal:2',
        'net_salary'                       => 'decimal:2',
        'is_locked'                        => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────────

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HungerStationFtrImportBatch::class, 'import_batch_id');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(HungerStationFtrDelegateDeduction::class, 'settlement_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Formula ──────────────────────────────────────────────────────────────────

    /**
     * Authoritative formula:
     *   net_salary = distance_payment
     *              − total_platform_penalties   (F+G+I+J+K+L+M+O)
     *              − abs(rider_balance)         (wallet already received)
     *              + company_benefits_total     (housing, transport, food…)
     *              − company_deductions_total   (fuel, loan, fines…)
     *
     * stacking_deduction (col H) is EXCLUDED — company absorbs it.
     * housing_allowance column on settlement is deprecated (always 0); benefits go through adjustments.
     */
    public function recalculate(): void
    {
        $this->total_platform_penalties = round(
            (float) $this->acceptance_rate_penalties
            + (float) $this->contact_rate_penalties
            + (float) $this->declined_penalties
            + (float) $this->late_penalty
            + (float) $this->no_show_penalty
            + (float) $this->no_show_penalty_special_cities
            + (float) $this->daily_acceptance_rate_penalty
            + (float) $this->missed_days_penalty,
            2
        );

        $this->company_benefits_total = round(
            (float) $this->deductions()->where('is_benefit', true)->sum('amount'),
            2
        );

        $this->company_deductions_total = round(
            (float) $this->deductions()->where('is_benefit', false)->sum('amount'),
            2
        );

        $this->net_salary = round(
            (float) $this->distance_payment
            - $this->total_platform_penalties
            - abs((float) $this->rider_balance)
            + $this->company_benefits_total
            - $this->company_deductions_total,
            2
        );

        $this->save();
    }
}
