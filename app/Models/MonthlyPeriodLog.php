<?php

namespace App\Models;

use App\Enums\PeriodAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPeriodLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_period_id',
        'action',
        'from_status',
        'to_status',
        'performed_by',
        'reason',
    ];

    protected $casts = [
        'action' => PeriodAction::class,
    ];

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
