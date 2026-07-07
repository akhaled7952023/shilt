<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegatePlatformAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'platform_id',
        'platform_delegate_id',
        'start_date',
        'end_date',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
