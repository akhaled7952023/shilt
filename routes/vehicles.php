<?php

use App\Http\Controllers\Dashboard\Vehicles\VehicleAssignmentController;
use App\Http\Controllers\Dashboard\Vehicles\VehicleController;
use App\Http\Controllers\Dashboard\Vehicles\VehicleDocumentController;
use App\Http\Controllers\Dashboard\Vehicles\VehicleMaintenanceController;
use App\Http\Controllers\Dashboard\Vehicles\VehicleRentalController;
use App\Http\Controllers\Dashboard\Vehicles\VehicleViolationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:vehicles'])
    ->prefix('dashboard/vehicles')
    ->name('dashboard.vehicles.')
    ->group(function () {
        // Main vehicle CRUD
        Route::resource('/', VehicleController::class)->parameters(['' => 'vehicle']);

        // Driver assignment — managed from vehicle show page
        Route::get('{vehicle}/assignments', [VehicleAssignmentController::class, 'index'])
            ->name('assignments.index');
        Route::post('{vehicle}/assignments', [VehicleAssignmentController::class, 'store'])
            ->name('assignments.store');
        Route::delete('{vehicle}/assignments/unassign', [VehicleAssignmentController::class, 'unassign'])
            ->name('assignments.unassign');

        // Maintenance — CRUD inside vehicle profile
        Route::post('{vehicle}/maintenance', [VehicleMaintenanceController::class, 'store'])
            ->name('maintenance.store');
        Route::get('{vehicle}/maintenance/{maintenance}/edit', [VehicleMaintenanceController::class, 'edit'])
            ->name('maintenance.edit');
        Route::put('{vehicle}/maintenance/{maintenance}', [VehicleMaintenanceController::class, 'update'])
            ->name('maintenance.update');
        Route::delete('{vehicle}/maintenance/{maintenance}', [VehicleMaintenanceController::class, 'destroy'])
            ->name('maintenance.destroy');

        // Violations — CRUD inside vehicle profile
        Route::post('{vehicle}/violations', [VehicleViolationController::class, 'store'])
            ->name('violations.store');
        Route::get('{vehicle}/violations/{violation}/edit', [VehicleViolationController::class, 'edit'])
            ->name('violations.edit');
        Route::put('{vehicle}/violations/{violation}', [VehicleViolationController::class, 'update'])
            ->name('violations.update');
        Route::delete('{vehicle}/violations/{violation}', [VehicleViolationController::class, 'destroy'])
            ->name('violations.destroy');

        // Dead routes — redirect to vehicle index or show
        Route::resource('{vehicle}/documents', VehicleDocumentController::class)
            ->only(['index', 'store', 'destroy']);
        Route::resource('{vehicle}/rentals', VehicleRentalController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });
