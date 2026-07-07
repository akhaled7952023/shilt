<?php

namespace App\Providers;

use App\Services\AuditService;
use App\Services\Calculation\ISettlementCalculationService;
use App\Services\Calculation\SettlementCalculationService;
use App\Services\Dashboard\Auth\AuthService;
use App\Services\Dashboard\Auth\IAuthService;
use App\Services\Dashboard\Dashboard\DashboardService;
use App\Services\Dashboard\Dashboard\IDashboardService;
use App\Services\Dashboard\Delegates\DelegateDocumentService;
use App\Services\Dashboard\Delegates\DelegateLeaveService;
use App\Services\Dashboard\Delegates\DelegateService;
use App\Services\Dashboard\Delegates\IDelegateDocumentService;
use App\Services\Dashboard\Delegates\IDelegateLeaveService;
use App\Services\Dashboard\Delegates\IDelegateService;
use App\Services\Dashboard\MasterData\Cities\CityService;
use App\Services\Dashboard\MasterData\Cities\ICityService;
use App\Services\Dashboard\MasterData\DocumentTypes\DocumentTypeService;
use App\Services\Dashboard\MasterData\DocumentTypes\IDocumentTypeService;
use App\Services\Dashboard\MasterData\LeaveTypes\ILeaveTypeService;
use App\Services\Dashboard\MasterData\LeaveTypes\LeaveTypeService;
use App\Services\Dashboard\MasterData\Platforms\IPlatformService;
use App\Services\Dashboard\MasterData\Platforms\PlatformService;
use App\Services\Dashboard\MasterData\VehicleTypes\IVehicleTypeService;
use App\Services\Dashboard\MasterData\VehicleTypes\VehicleTypeService;
use App\Services\Dashboard\MasterData\WarningTypes\IWarningTypeService;
use App\Services\Dashboard\MasterData\WarningTypes\WarningTypeService;
use App\Services\Dashboard\Monthly\AdvanceEntryService;
use App\Services\Dashboard\Monthly\CompanyDeductionService;
use App\Services\Dashboard\Monthly\DelegateMonthlyEntryService;
use App\Services\Dashboard\Monthly\FuelEntryService;
use App\Services\Dashboard\Monthly\IAdvanceEntryService;
use App\Services\Dashboard\Monthly\ICompanyDeductionService;
use App\Services\Dashboard\Monthly\IDelegateMonthlyEntryService;
use App\Services\Dashboard\Monthly\IFuelEntryService;
use App\Services\Dashboard\Monthly\ILeaveEntryService;
use App\Services\Dashboard\Monthly\IMonthlyPeriodService;
use App\Services\Dashboard\Monthly\IViolationEntryService;
use App\Services\Dashboard\Monthly\IWarningEntryService;
use App\Services\Dashboard\Monthly\LeaveEntryService;
use App\Services\Dashboard\Monthly\MonthlyPeriodService;
use App\Services\Dashboard\Monthly\ViolationEntryService;
use App\Services\Dashboard\Monthly\WarningEntryService;
use App\Services\Dashboard\Notes\DelegateNoteService;
use App\Services\Dashboard\Notes\IDelegateNoteService;
use App\Services\Dashboard\Reports\IReportService;
use App\Services\Dashboard\Reports\ReportService;
use App\Services\Dashboard\RolesAndManagers\Managers\IManagerServices;
use App\Services\Dashboard\RolesAndManagers\Managers\ManagerServices;
use App\Services\Dashboard\RolesAndManagers\Roles\IRolesServices;
use App\Services\Dashboard\RolesAndManagers\Roles\RolesServices;
use App\Services\Dashboard\Settings\ISystemSettingService;
use App\Services\Dashboard\Settings\SystemSettingService;
use App\Services\Dashboard\Vehicles\IVehicleAssignmentService;
use App\Services\Dashboard\Vehicles\IVehicleDocumentService;
use App\Services\Dashboard\Vehicles\IVehicleMaintenanceService;
use App\Services\Dashboard\Vehicles\IVehicleRentalService;
use App\Services\Dashboard\Vehicles\IVehicleService;
use App\Services\Dashboard\Vehicles\IVehicleViolationService;
use App\Services\Dashboard\Vehicles\VehicleAssignmentService;
use App\Services\Dashboard\Vehicles\VehicleDocumentService;
use App\Services\Dashboard\Vehicles\VehicleMaintenanceService;
use App\Services\Dashboard\Vehicles\VehicleRentalService;
use App\Services\Dashboard\Vehicles\VehicleService;
use App\Services\Dashboard\Vehicles\VehicleViolationService;
use App\Services\FileUploadService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth & Roles
        $this->app->bind(IAuthService::class, AuthService::class);
        $this->app->bind(IRolesServices::class, RolesServices::class);
        $this->app->bind(IManagerServices::class, ManagerServices::class);

        // Infrastructure
        $this->app->singleton(AuditService::class, AuditService::class);
        $this->app->singleton(FileUploadService::class, FileUploadService::class);
        $this->app->bind(ISettlementCalculationService::class, SettlementCalculationService::class);

        // Master Data
        $this->app->bind(ICityService::class, CityService::class);
        $this->app->bind(IPlatformService::class, PlatformService::class);
        $this->app->bind(IVehicleTypeService::class, VehicleTypeService::class);
        $this->app->bind(IDocumentTypeService::class, DocumentTypeService::class);
        $this->app->bind(IWarningTypeService::class, WarningTypeService::class);
        $this->app->bind(ILeaveTypeService::class, LeaveTypeService::class);

        // Delegates
        $this->app->bind(IDelegateService::class, DelegateService::class);
        $this->app->bind(IDelegateDocumentService::class, DelegateDocumentService::class);
        $this->app->bind(IDelegateLeaveService::class, DelegateLeaveService::class);

        // Vehicles
        $this->app->bind(IVehicleService::class, VehicleService::class);
        $this->app->bind(IVehicleDocumentService::class, VehicleDocumentService::class);
        $this->app->bind(IVehicleAssignmentService::class, VehicleAssignmentService::class);
        $this->app->bind(IVehicleRentalService::class, VehicleRentalService::class);
        $this->app->bind(IVehicleMaintenanceService::class, VehicleMaintenanceService::class);
        $this->app->bind(IVehicleViolationService::class, VehicleViolationService::class);

        // Monthly
        $this->app->bind(IMonthlyPeriodService::class, MonthlyPeriodService::class);
        $this->app->bind(IDelegateMonthlyEntryService::class, DelegateMonthlyEntryService::class);
        $this->app->bind(ICompanyDeductionService::class, CompanyDeductionService::class);
        $this->app->bind(IFuelEntryService::class, FuelEntryService::class);
        $this->app->bind(IAdvanceEntryService::class, AdvanceEntryService::class);
        $this->app->bind(ILeaveEntryService::class, LeaveEntryService::class);
        $this->app->bind(IViolationEntryService::class, ViolationEntryService::class);
        $this->app->bind(IWarningEntryService::class, WarningEntryService::class);

        // Operational
        $this->app->bind(IDelegateNoteService::class, DelegateNoteService::class);

        // Reports & Dashboard
        $this->app->bind(IReportService::class, ReportService::class);
        $this->app->bind(IDashboardService::class, DashboardService::class);

        // Settings
        $this->app->bind(ISystemSettingService::class, SystemSettingService::class);
    }

    public function boot(): void
    {
        //
    }
}
