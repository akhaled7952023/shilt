<?php

namespace App\Repositories\Dashboard\Settings;

use App\Models\SystemSettingLog;
use Illuminate\Support\Collection;

interface ISystemSettingLogRepository
{
    public function add(string $key, ?string $oldValue, string $newValue, int $userId): SystemSettingLog;

    public function getByKey(string $key): Collection;
}
