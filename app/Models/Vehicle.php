<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use App\Traits\Auditable;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, HasCreatedBy, Auditable;

    protected $fillable = [
        'plate_number',
        'make',
        'model',
        'year',
        'vehicle_type_id',
        'status',
        'color',
        'chassis_number',
        'vehicle_image',
        'registration_image',
        'insurance_image',
        'registration_number',
        'registration_issue_date',
        'registration_expiry_date',
        'insurance_company',
        'insurance_policy_number',
        'insurance_start_date',
        'insurance_expiry_date',
        'inspection_number',
        'inspection_issue_date',
        'inspection_expiry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'status'                  => VehicleStatus::class,
        'registration_issue_date' => 'date',
        'registration_expiry_date'=> 'date',
        'insurance_start_date'    => 'date',
        'insurance_expiry_date'   => 'date',
        'inspection_issue_date'   => 'date',
        'inspection_expiry_date'  => 'date',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)->where('is_active', true);
    }

    public function vehicleMaintenance()
    {
        return $this->hasMany(VehicleMaintenance::class)->orderByDesc('date');
    }

    public function vehicleViolations()
    {
        return $this->hasMany(VehicleViolation::class)->orderByDesc('date');
    }
}
