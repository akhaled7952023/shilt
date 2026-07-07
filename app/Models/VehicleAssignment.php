<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAssignment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'vehicle_id',
        'delegate_id',
        'assigned_at',
        'returned_at',
        'is_active',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'returned_at' => 'date',
        'is_active'   => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }
}
