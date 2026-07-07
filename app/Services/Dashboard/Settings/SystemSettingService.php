<?php

namespace App\Services\Dashboard\Settings;

use App\Models\SystemSetting;
use App\Repositories\Dashboard\Settings\ISystemSettingLogRepository;
use App\Repositories\Dashboard\Settings\ISystemSettingRepository;
use App\Services\AuditService;
use Illuminate\Support\Collection;

class SystemSettingService implements ISystemSettingService
{
    public function __construct(
        private ISystemSettingRepository    $repository,
        private ISystemSettingLogRepository $logRepository,
        private AuditService                $auditService
    ) {}

    public function get(string $key, $default = null)
    {
        $setting = $this->repository->getByKey($key);

        if ($setting === null) {
            return $default;
        }

        return $setting->getValue();
    }

    public function set(string $key, $value, int $userId): void
    {
        $old      = $this->repository->getByKey($key);
        $oldValue = $old?->value;

        $setting = $this->repository->upsert($key, (string) $value, $userId);

        // Dedicated settings audit trail
        $this->logRepository->add($key, $oldValue, (string) $value, $userId);

        // General audit log
        $this->auditService->log(
            'updated',
            $setting,
            ['value' => $oldValue],
            ['value' => (string) $value]
        );
    }

    public function all(): Collection
    {
        return $this->repository->getAll();
    }

    public function getSetting(string $key): ?SystemSetting
    {
        return $this->repository->getByKey($key);
    }

    public function getGrouped(): Collection
    {
        return $this->repository->getAll()->groupBy('group');
    }

    public function getHistory(string $key): Collection
    {
        return $this->logRepository->getByKey($key);
    }
}
