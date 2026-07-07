<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'monthly_period_id',
        'description',
        'amount',
        'violation_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'amount'         => 'decimal:2',
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
