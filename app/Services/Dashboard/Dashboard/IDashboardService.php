<?php
namespace App\Services\Dashboard\Dashboard;

interface IDashboardService
{
    public function getAdminDashboardData(): array;
    public function getDelegateDashboardData(int $delegateId): array;
}
