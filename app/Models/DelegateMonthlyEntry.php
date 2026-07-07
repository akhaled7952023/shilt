<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegateMonthlyEntry extends Model
{
    use HasFactory, HasCreatedBy, Auditable;

    protected $fillable = [
        'delegate_id',
        'monthly_period_id',
        'platform_id',
        'orders_count',
        'eligible_km',
        'distance_pay',
        'tips',
        'grants',
        'housing_allowance',
        'bonus',
        'fuel_deduction',
        'wallet_deduction',
        'app_deduction',
        'previous_carry_over',
        'short_distance_penalty',
        'gross_entitlement',
        'total_deductions',
        'net_settlement',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'eligible_km'           => 'decimal:2',
        'distance_pay'          => 'decimal:2',
        'tips'                  => 'decimal:2',
        'grants'                => 'decimal:2',
        'housing_allowance'     => 'decimal:2',
        'bonus'                 => 'decimal:2',
        'fuel_deduction'        => 'decimal:2',
        'wallet_deduction'      => 'decimal:2',
        'app_deduction'         => 'decimal:2',
        'previous_carry_over'   => 'decimal:2',
        'short_distance_penalty'=> 'decimal:2',
        'gross_entitlement'     => 'decimal:2',
        'total_deductions'      => 'decimal:2',
        'net_settlement'        => 'decimal:2',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function companyDeductions()
    {
        return $this->hasMany(CompanyDeduction::class);
    }
}
