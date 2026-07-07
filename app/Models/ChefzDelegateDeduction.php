<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChefzDelegateDeduction extends Model
{
    public const TYPE_LABELS = [
        'fuel'               => 'خصم البنزين',
        'wallet'             => 'المحفظة',
        'advance'            => 'سلفة',
        'app_penalty'        => 'خصم التطبيق',
        'company_penalty'    => 'خصم الشركة',
        'previous_balance'   => 'مبالغ سابقة',
        'distance_deduction' => 'خصم المسافات',
        'traffic_violation'  => 'مخالفة مرورية',
        'other'              => 'أخرى',
    ];

    public function getTypeLabel(): string
    {
        if ($this->deduction_type === 'other' && $this->label) {
            return $this->label;
        }
        $key = 'portal.ded_cz_' . $this->deduction_type;
        $translated = __($key);
        if ($translated !== $key) {
            return $translated;
        }
        return self::TYPE_LABELS[$this->deduction_type] ?? $this->deduction_type;
    }

    protected $fillable = [
        'settlement_id',
        'monthly_period_id',
        'delegate_id',
        'deduction_type',
        'label',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ChefzDelegateSettlement::class, 'settlement_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
