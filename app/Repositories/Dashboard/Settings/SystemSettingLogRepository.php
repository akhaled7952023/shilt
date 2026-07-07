<?php

namespace App\Repositories\Dashboard\Settings;

use App\Models\SystemSettingLog;
use Illuminate\Support\Collection;

class SystemSettingLogRepository implements ISystemSettingLogRepository
{
    public function add(string $key, ?string $oldValue, string $newValue, int $userId): SystemSettingLog
    {
        return SystemSettingLog::create([
            'setting_key' => $key,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
            'changed_by'  => $userId,
            'changed_at'  => now(),
        ]);
    }

    public function getByKey(string $key): Collection
    {
        return SystemSettingLog::where('setting_key', $key)
            ->with('changedBy')
            ->orderByDesc('changed_at')
            ->get();
    }
}
