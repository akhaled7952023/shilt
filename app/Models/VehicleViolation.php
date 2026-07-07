<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleViolation extends Model
{
    use HasFactory, HasCreatedBy;

    protected $fillable = [
        'vehicle_id',
        'delegate_id',
        'warning_type_id',
        'location',
        'date',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function warningType()
    {
        return $this->belongsTo(WarningType::class);
    }
}
