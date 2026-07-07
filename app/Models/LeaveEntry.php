<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveEntry extends Model
{
    use HasFactory, HasCreatedBy;

    protected $fillable = [
        'delegate_id',
        'monthly_period_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
