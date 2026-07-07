<?php
namespace App\Services\Dashboard\Dashboard;

class DashboardService implements IDashboardService
{
    public function getAdminDashboardData(): array
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getDelegateDashboardData(int $delegateId): array
    {
        throw new \RuntimeException('Not implemented');
    }
}
