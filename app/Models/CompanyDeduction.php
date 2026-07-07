<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_monthly_entry_id',
        'deduction_category_id',
        'amount',
        'reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function delegateMonthlyEntry()
    {
        return $this->belongsTo(DelegateMonthlyEntry::class);
    }

    public function deductionCategory()
    {
        return $this->belongsTo(DeductionCategory::class);
    }
}
