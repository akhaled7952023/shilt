<?php

namespace App\Providers;

use App\Repositories\Dashboard\Auth\AuthRepository;
use App\Repositories\Dashboard\Auth\IAuthRepository;
use App\Repositories\Dashboard\AuditLogs\AuditLogRepository;
use App\Repositories\Dashboard\AuditLogs\IAuditLogRepository;
use App\Repositories\Dashboard\Delegates\DelegateLeaveRepository;
use App\Repositories\Dashboard\Delegates\DelegateRepository;
use App\Repositories\Dashboard\Delegates\IDelegateLeaveRepository;
use App\Repositories\Dashboard\Delegates\IDelegateRepository;
use App\Repositories\Dashboard\MasterData\Cities\CityRepository;
use App\Repositories\Dashboard\MasterData\Cities\ICityRepository;
use App\Repositories\Dashboard\MasterData\DocumentTypes\DocumentTypeRepository;
use App\Repositories\Dashboard\MasterData\DocumentTypes\IDocumentTypeRepository;
use App\Repositories\Dashboard\MasterData\LeaveTypes\ILeaveTypeRepository;
use App\Repositories\Dashboard\MasterData\LeaveTypes\LeaveTypeRepository;
use App\Repositories\Dashboard\MasterData\Platforms\IPlatformRepository;
use App\Repositories\Dashboard\MasterData\Platforms\PlatformRepository;
use App\Repositories\Dashboard\MasterData\VehicleTypes\IVehicleTypeRepository;
use App\Repositories\Dashboard\MasterData\VehicleTypes\VehicleTypeRepository;
use App\Repositories\Dashboard\MasterData\WarningTypes\IWarningTypeRepository;
use App\Repositories\Dashboard\MasterData\WarningTypes\WarningTypeRepository;
use App\Repositories\Dashboard\Monthly\DelegateMonthlyEntryRepository;
use App\Repositories\Dashboard\Monthly\IDelegateMonthlyEntryRepository;
use App\Repositories\Dashboard\Monthly\IMonthlyPeriodRepository;
use App\Repositories\Dashboard\Monthly\MonthlyPeriodRepository;
use App\Repositories\Dashboard\RolesAndManagers\Managers\IManagersRepository;
use App\Repositories\Dashboard\RolesAndManagers\Managers\ManagersRepository;
use App\Repositories\Dashboard\RolesAndManagers\Roles\IRolesRepository;
use App\Repositories\Dashboard\RolesAndManagers\Roles\RolesRepository;
use App\Repositories\Dashboard\Settings\ISystemSettingLogRepository;
use App\Repositories\Dashboard\Settings\ISystemSettingRepository;
use App\Repositories\Dashboard\Settings\SystemSettingLogRepository;
use App\Repositories\Dashboard\Settings\SystemSettingRepository;
use App\Repositories\Dashboard\VehicleAssignments\IVehicleAssignmentRepository;
use App\Repositories\Dashboard\VehicleAssignments\VehicleAssignmentRepository;
use App\Repositories\Dashboard\Vehicles\IVehicleMaintenanceRepository;
use App\Repositories\Dashboard\Vehicles\IVehicleRepository;
use App\Repositories\Dashboard\Vehicles\IVehicleViolationRepository;
use App\Repositories\Dashboard\Vehicles\VehicleMaintenanceRepository;
use App\Repositories\Dashboard\Vehicles\VehicleRepository;
use App\Repositories\Dashboard\Vehicles\VehicleViolationRepository;
use Illuminate\Support\ServiceProvider;

class ReposiotryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth & Roles
        $this->app->bind(IAuthRepository::class, AuthRepository::class);
        $this->app->bind(IRolesRepository::class, RolesRepository::class);
        $this->app->bind(IManagersRepository::class, ManagersRepository::class);

        // Core
        $this->app->bind(IDelegateRepository::class, DelegateRepository::class);
        $this->app->bind(IDelegateLeaveRepository::class, DelegateLeaveRepository::class);
        $this->app->bind(IVehicleRepository::class, VehicleRepository::class);
        $this->app->bind(IMonthlyPeriodRepository::class, MonthlyPeriodRepository::class);
        $this->app->bind(IDelegateMonthlyEntryRepository::class, DelegateMonthlyEntryRepository::class);
        $this->app->bind(IVehicleAssignmentRepository::class, VehicleAssignmentRepository::class);
        $this->app->bind(IVehicleMaintenanceRepository::class, VehicleMaintenanceRepository::class);
        $this->app->bind(IVehicleViolationRepository::class, VehicleViolationRepository::class);
        $this->app->bind(IAuditLogRepository::class, AuditLogRepository::class);

        // Settings
        $this->app->bind(ISystemSettingRepository::class, SystemSettingRepository::class);
        $this->app->bind(ISystemSettingLogRepository::class, SystemSettingLogRepository::class);

        // Master Data Repositories
        $this->app->bind(ICityRepository::class, CityRepository::class);
        $this->app->bind(IPlatformRepository::class, PlatformRepository::class);
        $this->app->bind(IVehicleTypeRepository::class, VehicleTypeRepository::class);
        $this->app->bind(IDocumentTypeRepository::class, DocumentTypeRepository::class);
        $this->app->bind(IWarningTypeRepository::class, WarningTypeRepository::class);
        $this->app->bind(ILeaveTypeRepository::class, LeaveTypeRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
