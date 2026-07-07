<?php

namespace App\Repositories\Dashboard\Settings;

use App\Models\SystemSetting;
use Illuminate\Support\Collection;

interface ISystemSettingRepository
{
    public function getAll(): Collection;

    public function getByKey(string $key): ?SystemSetting;

    public function upsert(string $key, string $value, ?int $userId = null): SystemSetting;
}
