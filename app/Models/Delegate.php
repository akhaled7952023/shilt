<?php

namespace App\Models;

use App\Enums\DelegateStatus;
use App\Traits\Auditable;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Delegate extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasCreatedBy, Auditable;

    protected $fillable = [
        'delegate_code',
        'name',
        'national_id',
        'phone',
        'email',
        'password',
        'city_id',
        'platform_id',
        'status',
        'hire_date',
        'termination_date',
        'profile_photo',
        'iqama_image',
        'driving_license_image',
        'bank_name',
        'iban',
        'notes',
        'needs_review',
        'created_by',
        'portal_password',
        'portal_enabled',
        'portal_first_login',
        'last_portal_login',
        'hungerstation_rider_id',
    ];

    protected $hidden = [
        'password',
        'portal_password',
        'remember_token',
    ];

    protected $casts = [
        'status'             => DelegateStatus::class,
        'hire_date'          => 'date',
        'termination_date'   => 'date',
        'password'           => 'hashed',
        'portal_enabled'     => 'boolean',
        'portal_first_login' => 'boolean',
        'last_portal_login'  => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->portal_password ?? '';
    }

    public function setPortalPassword(string $plaintext): void
    {
        $this->update([
            'portal_password'    => Hash::make($plaintext),
            'portal_first_login' => true,
        ]);
    }

    // Relationships

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function delegateDocuments()
    {
        return $this->hasMany(DelegateDocument::class);
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function delegateMonthlyEntries()
    {
        return $this->hasMany(DelegateMonthlyEntry::class);
    }

    public function fuelEntries()
    {
        return $this->hasMany(FuelEntry::class);
    }

    public function advanceEntries()
    {
        return $this->hasMany(AdvanceEntry::class);
    }

    public function leaveEntries()
    {
        return $this->hasMany(LeaveEntry::class);
    }

    public function violationEntries()
    {
        return $this->hasMany(ViolationEntry::class);
    }

    public function warningEntries()
    {
        return $this->hasMany(WarningEntry::class);
    }

    public function delegateNotes()
    {
        return $this->hasMany(DelegateNote::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', DelegateStatus::Active);
    }

    public function scopeByStatus($query, DelegateStatus $status)
    {
        return $query->where('status', $status);
    }
}
