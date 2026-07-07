<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'monthly_period_id',
        'amount_sar',
        'entry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount_sar' => 'decimal:2',
        'entry_date' => 'date',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }
}
