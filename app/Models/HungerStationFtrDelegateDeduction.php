<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HungerStationFtrDelegateDeduction extends Model
{
    protected $table = 'hungerstation_ftr_delegate_deductions';

    // خصومات الشركة (تُخصم من الراتب) — القائمة المعتمدة فقط
    public const DEDUCTION_TYPE_LABELS = [
        'fuel'              => 'وقود',
        'advance'           => 'سلفة',
        'traffic_violation' => 'مخالفة مرورية',
        'company_penalty'   => 'غرامة داخلية',
        'loan'              => 'مبالغ سابقة',
    ];

    // مزايا الشركة (تُضاف للراتب) — القائمة المعتمدة فقط
    public const BENEFIT_TYPE_LABELS = [
        'housing_allowance'   => 'بدل سكن',
        'transport_allowance' => 'إكرامية',
        'food_allowance'      => 'مستحقات',
        'other_benefit'       => 'منح الشركة',
    ];

    // جدول الترجمة الشامل — للسجلات القديمة
    public const TYPE_LABELS = [
        'fuel'              => 'وقود',
        'iqama'             => 'إقامة',
        'advance'           => 'سلفة',
        'loan'              => 'مبالغ سابقة',
        'vehicle'           => 'تلف مركبة',
        'app_penalty'       => 'غرامة تطبيق',
        'company_penalty'   => 'غرامة داخلية',
        'traffic_violation' => 'مخالفة مرورية',
        'other'             => 'أخرى',
        'housing_allowance'   => 'بدل سكن',
        'transport_allowance' => 'إكرامية',
        'food_allowance'      => 'مستحقات',
        'other_benefit'       => 'منح الشركة',
    ];

    protected $fillable = [
        'settlement_id',
        'monthly_period_id',
        'delegate_id',
        'deduction_type',
        'is_benefit',
        'label',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'is_benefit' => 'boolean',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(HungerStationFtrSettlement::class, 'settlement_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabel(): string
    {
        if (in_array($this->deduction_type, ['other', 'other_benefit'])) {
            return $this->label;
        }
        $key = 'portal.ded_hs_' . $this->deduction_type;
        $translated = __($key);
        if ($translated !== $key) {
            return $translated;
        }
        $map = $this->is_benefit ? self::BENEFIT_TYPE_LABELS : self::DEDUCTION_TYPE_LABELS;
        return $map[$this->deduction_type] ?? self::TYPE_LABELS[$this->deduction_type] ?? $this->label;
    }
}
