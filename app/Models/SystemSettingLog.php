<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSettingLog extends Model
{
    protected $table = 'system_settings_log';

    public $timestamps = false;

    protected $fillable = [
        'setting_key',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
