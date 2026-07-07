<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function delegatePlatformAssignments()
    {
        return $this->hasMany(DelegatePlatformAssignment::class);
    }

    public function delegateMonthlyEntries()
    {
        return $this->hasMany(DelegateMonthlyEntry::class);
    }
}
