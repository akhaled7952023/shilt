<?php

namespace App\Models;

use App\Enums\SettingType;
use App\Repositories\Dashboard\Settings\ISystemSettingRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'description',
        'group',
        'is_public',
        'updated_by',
    ];

    protected $casts = [
        'type'      => SettingType::class,
        'is_public' => 'boolean',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Value Accessors ────────────────────────────────────────────────────────

    public function getValue(): mixed
    {
        return match ($this->type) {
            SettingType::Decimal  => (float) $this->value,
            SettingType::Integer  => (int) $this->value,
            SettingType::Boolean  => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            SettingType::Json     => json_decode($this->value, true),
            default               => $this->value,
        };
    }

    // ─── Static Helper ──────────────────────────────────────────────────────────

    /**
     * Read and cast a setting value by key.
     * Delegates to the repository which caches each key for 5 minutes.
     */
    public static function get(string $key): mixed
    {
        return app(ISystemSettingRepository::class)->getByKey($key)?->getValue();
    }
}
