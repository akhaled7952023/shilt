<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarningEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'monthly_period_id',
        'warning_type_id',
        'description',
        'warning_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'warning_date' => 'date',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }

    public function warningType()
    {
        return $this->belongsTo(WarningType::class);
    }
}
