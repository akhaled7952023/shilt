<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_type_id',
        'title',
        'body',
        'monthly_period_id',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function announcementType()
    {
        return $this->belongsTo(AnnouncementType::class);
    }

    public function monthlyPeriod()
    {
        return $this->belongsTo(MonthlyPeriod::class);
    }
}
