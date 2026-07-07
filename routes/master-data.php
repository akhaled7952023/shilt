<?php

use App\Http\Controllers\Dashboard\MasterData\CityController;
use App\Http\Controllers\Dashboard\MasterData\DocumentTypeController;
use App\Http\Controllers\Dashboard\MasterData\LeaveTypeController;
use App\Http\Controllers\Dashboard\MasterData\PlatformController;
use App\Http\Controllers\Dashboard\MasterData\VehicleTypeController;
use App\Http\Controllers\Dashboard\MasterData\WarningTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:master-data'])
    ->prefix('dashboard/master-data')
    ->name('dashboard.master-data.')
    ->group(function () {
        Route::resource('cities', CityController::class);
        Route::patch('cities/{city}/toggle', [CityController::class, 'toggle'])
            ->name('cities.toggle');

        Route::get('platforms', [PlatformController::class, 'index'])
            ->name('platforms.index');

        Route::resource('vehicle-types', VehicleTypeController::class);
        Route::patch('vehicle-types/{vehicle_type}/toggle', [VehicleTypeController::class, 'toggle'])
            ->name('vehicle-types.toggle');

        Route::resource('document-types', DocumentTypeController::class);
        Route::patch('document-types/{document_type}/toggle', [DocumentTypeController::class, 'toggle'])
            ->name('document-types.toggle');

        Route::resource('warning-types', WarningTypeController::class);
        Route::patch('warning-types/{warning_type}/toggle', [WarningTypeController::class, 'toggle'])
            ->name('warning-types.toggle');

        Route::resource('leave-types', LeaveTypeController::class);
        Route::patch('leave-types/{leave_type}/toggle', [LeaveTypeController::class, 'toggle'])
            ->name('leave-types.toggle');
    });
