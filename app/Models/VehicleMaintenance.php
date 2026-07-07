<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenance extends Model
{
    use HasFactory, HasCreatedBy;

    protected $table = 'vehicle_maintenance';

    protected $fillable = [
        'vehicle_id',
        'date',
        'description',
        'cost',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'     => 'معلق',
            'in_progress' => 'جارٍ',
            'completed'   => 'مكتمل',
            default       => $this->status,
        };
    }
}
