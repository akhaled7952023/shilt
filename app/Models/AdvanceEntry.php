<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'monthly_period_id',
        'amount',
        'entry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
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
