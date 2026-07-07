<?php

namespace App\Services\Dashboard\Settings;

use App\Models\SystemSetting;
use Illuminate\Support\Collection;

interface ISystemSettingService
{
    public function get(string $key, $default = null);

    public function set(string $key, $value, int $userId): void;

    public function all(): Collection;

    public function getSetting(string $key): ?SystemSetting;

    public function getGrouped(): Collection;

    public function getHistory(string $key): Collection;
}
