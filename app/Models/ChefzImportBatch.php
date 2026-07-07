<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChefzImportBatch extends Model
{
    protected $fillable = [
        'monthly_period_id',
        'payout_number',
        'original_filename',
        'file_path',
        'file_size_bytes',
        'total_rows',
        'skipped_duplicates',
        'unique_delegates',
        'new_delegates_created',
        'error_count',
        'warning_count',
        'import_duration_ms',
        'status',
        'version_number',
        'error_message',
        'imported_by',
        'imported_at',
    ];

    protected $casts = [
        'imported_at'   => 'datetime',
        'payout_number' => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ChefzOrder::class, 'import_batch_id');
    }

    public function getPayoutLabel(): string
    {
        return match ($this->payout_number) {
            1 => 'الدفعة الأولى',
            2 => 'الدفعة الثانية',
            default => "دفعة {$this->payout_number}",
        };
    }
}
