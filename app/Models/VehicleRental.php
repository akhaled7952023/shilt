<?php

namespace App\Models;

use App\Enums\PaymentBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleRental extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'delegate_id',
        'monthly_period_id',
        'payment_by',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_by' => PaymentBy::class,
        'amount'     => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }
}
