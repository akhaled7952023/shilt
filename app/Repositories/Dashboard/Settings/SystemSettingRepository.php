<?php

namespace App\Repositories\Dashboard\Settings;

use App\Models\SystemSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SystemSettingRepository implements ISystemSettingRepository
{
    public function getAll(): Collection
    {
        return SystemSetting::orderBy('group')->orderBy('key')->get();
    }

    public function getByKey(string $key): ?SystemSetting
    {
        return Cache::remember('setting.' . $key, 300, function () use ($key) {
            return SystemSetting::where('key', $key)->first();
        });
    }

    public function upsert(string $key, string $value, ?int $userId = null): SystemSetting
    {
        Cache::forget('setting.' . $key);

        $update = ['value' => $value];

        if ($userId !== null) {
            $update['updated_by'] = $userId;
        }

        return SystemSetting::updateOrCreate(
            ['key' => $key],
            $update
        );
    }
}
