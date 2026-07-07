<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HungerStationFtrImportBatch extends Model
{
    protected $table = 'hungerstation_ftr_import_batches';

    protected $fillable = [
        'monthly_period_id',
        'original_filename',
        'file_path',
        'file_size_bytes',
        'total_riders',
        'matched_delegates',
        'unmatched_riders',
        'basic_payment_total',
        'distance_payment_total',
        'rider_balance_total',
        'status',
        'import_duration_ms',
        'error_message',
        'imported_by',
        'imported_at',
    ];

    protected $casts = [
        'basic_payment_total'    => 'decimal:2',
        'distance_payment_total' => 'decimal:2',
        'rider_balance_total'    => 'decimal:2',
        'imported_at'            => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(HungerStationFtrSettlement::class, 'import_batch_id');
    }
}
