<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use App\Traits\Auditable;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPeriod extends Model
{
    use HasFactory, HasCreatedBy, Auditable;

    protected $fillable = [
        'platform_id',
        'year',
        'month',
        'label',
        'status',
        'opened_at',
        'opened_by',
        'approved_at',
        'approved_by',
        'published_at',
        'published_by',
        'closed_at',
        'closed_by',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'status'       => PeriodStatus::class,
        'opened_at'    => 'datetime',
        'approved_at'  => 'datetime',
        'published_at' => 'datetime',
        'closed_at'    => 'datetime',
    ];

    private static array $ARABIC_MONTHS = [
        1  => 'يناير',
        2  => 'فبراير',
        3  => 'مارس',
        4  => 'أبريل',
        5  => 'مايو',
        6  => 'يونيو',
        7  => 'يوليو',
        8  => 'أغسطس',
        9  => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    private static array $ENGLISH_MONTHS = [
        1  => 'January',
        2  => 'February',
        3  => 'March',
        4  => 'April',
        5  => 'May',
        6  => 'June',
        7  => 'July',
        8  => 'August',
        9  => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function monthlyPeriodLogs()
    {
        return $this->hasMany(MonthlyPeriodLog::class);
    }

    public function delegateMonthlyEntries()
    {
        return $this->hasMany(DelegateMonthlyEntry::class);
    }

    public function vehicleRentals()
    {
        return $this->hasMany(VehicleRental::class);
    }

    // ── Status helpers ───────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === PeriodStatus::Open;
    }

    public function isClosed(): bool
    {
        return $this->status === PeriodStatus::Closed;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [PeriodStatus::Open, PeriodStatus::Editing]);
    }

    // ── Label helpers ────────────────────────────────────────────────────────

    /** Returns a locale-aware period label, e.g. "May 2026" (en) or "مايو 2026" (ar). */
    public function getDisplayLabel(): string
    {
        if (app()->getLocale() === 'en') {
            return (static::$ENGLISH_MONTHS[$this->month] ?? (string) $this->month) . ' ' . $this->year;
        }
        return $this->label ?? static::makeLabel($this->month, $this->year);
    }

    /** Generates the Arabic month-year label for storage. */
    public static function makeLabel(int $month, int $year): string
    {
        return (static::$ARABIC_MONTHS[$month] ?? (string) $month) . ' ' . $year;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->where('status', PeriodStatus::Open->value);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', PeriodStatus::Closed->value);
    }

    // ── Static helpers ───────────────────────────────────────────────────────

    public static function current(): ?static
    {
        return static::open()->orderByDesc('year')->orderByDesc('month')->first();
    }
}
