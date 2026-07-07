<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'document_type_id',
        'file_path',
        'issue_date',
        'expiry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function scopeExpiringBefore($query, $date)
    {
        return $query->where('expiry_date', '<=', $date);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}
