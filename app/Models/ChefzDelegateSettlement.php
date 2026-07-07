<?php

namespace App\Models;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ChefzDelegateSettlement extends Model
{
    protected $fillable = [
        'monthly_period_id',
        'delegate_id',
        'payout_number',
        'total_orders',
        'gross_delivery_fees',
        'platform_deductions',
        'platform_compensations',
        'bonus_total',
        'positive_bonus',
        'chefz_tax_rate',
        'chefz_tax_amount',
        'company_share_rate',
        'company_share_amount',
        'commission_total',
        'deductions_total',
        'net_salary',
        'is_locked',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_locked'              => 'boolean',
        'payout_number'          => 'integer',
        'gross_delivery_fees'    => 'decimal:2',
        'platform_deductions'    => 'decimal:2',
        'platform_compensations' => 'decimal:2',
        'bonus_total'            => 'decimal:2',
        'positive_bonus'         => 'decimal:2',
        'chefz_tax_rate'         => 'decimal:4',
        'chefz_tax_amount'       => 'decimal:2',
        'company_share_rate'     => 'decimal:4',
        'company_share_amount'   => 'decimal:2',
        'commission_total'       => 'decimal:2',
        'deductions_total'       => 'decimal:2',
        'net_salary'             => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(ChefzDelegateDeduction::class, 'settlement_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Re-aggregate from chefz_orders (scoped to this payout_number), then recompute financials.
     */
    public function recalculateFromOrders(): void
    {
        $agg = DB::table('chefz_orders')
            ->where('monthly_period_id', $this->monthly_period_id)
            ->where('delegate_id', $this->delegate_id)
            ->where('payout_number', $this->payout_number)
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(delivery_fee), 0)               as gross_delivery_fees,
                COALESCE(SUM(deduction_amount), 0)           as platform_deductions,
                COALESCE(SUM(compensation), 0)               as platform_compensations,
                COALESCE(SUM(bonus_amount), 0)               as bonus_total,
                COALESCE(SUM(GREATEST(bonus_amount, 0)), 0)  as positive_bonus
            ')
            ->first();

        $this->total_orders           = $agg->total_orders;
        $this->gross_delivery_fees    = round($agg->gross_delivery_fees, 2);
        $this->platform_deductions    = round($agg->platform_deductions, 2);
        $this->platform_compensations = round($agg->platform_compensations, 2);
        $this->bonus_total            = round($agg->bonus_total, 2);
        $this->positive_bonus         = round($agg->positive_bonus, 2);
        $this->save();

        $this->recalculate();
    }

    /**
     * Recompute financial totals using Mahmoud's approved Chefz formula.
     *
     * Step 1: vatAmount    = gross × vat_rate                           (VAT from Settings)
     * Step 2: driverBase   = gross − vatAmount                          (base before any additions)
     * Step 3: subtotal     = driverBase + compensations + positiveBonus (negative bonus = cancelled, not deducted)
     * Step 4: companyShare = subtotal × commission_rate                 (rate from Settings, applied on subtotal)
     * Step 5: deductions   = platform_deductions + manual_deductions    (deductions LAST)
     * Step 6: net          = subtotal − companyShare − deductions
     */
    public function recalculate(): void
    {
        $vatRate        = (float) (SystemSetting::get('chefz_vat_rate')        ?? 0);
        $commissionRate = (float) (SystemSetting::get('chefz_commission_rate') ?? 0);

        $gross = (float) $this->gross_delivery_fees;

        // Step 1: VAT
        $vatAmount = round($gross * $vatRate, 2);

        // Step 2: Driver Base = Gross − VAT (company share not yet deducted)
        $driverBase = round($gross - $vatAmount, 2);

        // Step 3: Subtotal = Driver Base + Compensations + Positive Bonus
        $positiveBonus = max(0.0, (float) $this->positive_bonus);
        $subtotal      = round($driverBase + (float) $this->platform_compensations + $positiveBonus, 2);

        // Step 4: Company Share = Subtotal × Rate  (applied on the full subtotal)
        $companyShareAmount = round($subtotal * $commissionRate, 2);

        // Step 5: Deductions (platform + manual) — subtracted LAST
        $manualDeductions = (float) $this->deductions()->sum('amount');
        $deductionsTotal  = round((float) $this->platform_deductions + $manualDeductions, 2);

        // Step 6: Net = Subtotal − Company Share − Deductions
        $netSalary = round($subtotal - $companyShareAmount - $deductionsTotal, 2);

        $this->chefz_tax_rate       = $vatRate;
        $this->chefz_tax_amount     = $vatAmount;
        $this->company_share_rate   = $commissionRate;
        $this->company_share_amount = $companyShareAmount;
        $this->commission_total     = $subtotal;   // subtotal before company share
        $this->deductions_total     = $deductionsTotal;
        $this->net_salary           = $netSalary;

        $this->save();
    }
}
