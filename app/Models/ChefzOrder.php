<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChefzOrder extends Model
{
    protected $fillable = [
        'import_batch_id',
        'monthly_period_id',
        'delegate_id',
        'payout_number',
        'raw_driver_id',
        'raw_driver_name',
        'order_date',
        'order_id_platform',
        'delivery_fee',
        'deduction_amount',
        'deduction_note',
        'compensation',
        'compensation_note',
        'bonus_amount',
        'bonus_note',
    ];

    protected $casts = [
        'order_date'       => 'date',
        'delivery_fee'     => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'compensation'     => 'decimal:2',
        'bonus_amount'     => 'decimal:2',
        'payout_number'    => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ChefzImportBatch::class, 'import_batch_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(MonthlyPeriod::class, 'monthly_period_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }
}
