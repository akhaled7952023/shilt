<?php

use App\Http\Controllers\Dashboard\Monthly\AdvanceEntryController;
use App\Http\Controllers\Dashboard\Monthly\CompanyDeductionController;
use App\Http\Controllers\Dashboard\Monthly\CompanyExpenseController;
use App\Http\Controllers\Dashboard\Monthly\DelegateMonthlyEntryController;
use App\Http\Controllers\Dashboard\Monthly\FuelEntryController;
use App\Http\Controllers\Dashboard\Monthly\LeaveEntryController;
use App\Http\Controllers\Dashboard\Monthly\MonthlyFinancialDashboardController;
use App\Http\Controllers\Dashboard\Monthly\MonthlyPeriodController;
use App\Http\Controllers\Dashboard\Monthly\ViolationEntryController;
use App\Http\Controllers\Dashboard\Monthly\WarningEntryController;
use App\Http\Controllers\Dashboard\Chefz\ChefzImportController;
use App\Http\Controllers\Dashboard\Chefz\ChefzSettlementController;
use App\Http\Controllers\Dashboard\HungerStation\HungerStationFtrImportController;
use App\Http\Controllers\Dashboard\HungerStation\HungerStationFtrSettlementController;
use App\Http\Controllers\Dashboard\HungerStation\PendingEntryImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:monthly-periods'])
    ->prefix('dashboard/monthly')
    ->name('dashboard.monthly.')
    ->group(function () {
        // Period CRUD
        Route::resource('periods', MonthlyPeriodController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy']);

        // Period lifecycle actions
        Route::post('periods/{period}/close', [MonthlyPeriodController::class, 'close'])
            ->name('periods.close');
        Route::post('periods/{period}/reopen', [MonthlyPeriodController::class, 'reopen'])
            ->name('periods.reopen');

        // Entries within a period
        Route::prefix('periods/{period}')->name('periods.')->group(function () {
            Route::resource('entries', DelegateMonthlyEntryController::class)
                ->only(['index', 'show']);
            Route::post('entries/upsert', [DelegateMonthlyEntryController::class, 'createOrUpdate'])
                ->name('entries.upsert');
            Route::post('entries/{entry}/recalculate', [DelegateMonthlyEntryController::class, 'recalculate'])
                ->name('entries.recalculate');
            Route::post('entries/{entry}/deductions', [CompanyDeductionController::class, 'sync'])
                ->name('entries.deductions.sync');
            Route::resource('fuel', FuelEntryController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::resource('advances', AdvanceEntryController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::resource('leaves', LeaveEntryController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::resource('violations', ViolationEntryController::class)
                ->only(['index', 'store', 'update', 'destroy']);
            Route::resource('warnings', WarningEntryController::class)
                ->only(['index', 'store', 'update', 'destroy']);

            // Company Expenses — HungerStation only
            Route::get('expenses', [CompanyExpenseController::class, 'index'])
                ->name('expenses.index');
            Route::post('expenses', [CompanyExpenseController::class, 'store'])
                ->name('expenses.store');
            Route::put('expenses/{expense}', [CompanyExpenseController::class, 'update'])
                ->name('expenses.update');
            Route::delete('expenses/{expense}', [CompanyExpenseController::class, 'destroy'])
                ->name('expenses.destroy');

            // Monthly Financial Dashboard
            Route::get('financial-dashboard', [MonthlyFinancialDashboardController::class, 'show'])
                ->name('financial-dashboard');
            Route::get('financial-dashboard/pdf', [MonthlyFinancialDashboardController::class, 'pdf'])
                ->name('financial-dashboard.pdf');

            // HungerStation FTR import pipeline (replaces retired commission import)
            Route::prefix('hungerstation/ftr')->name('hungerstation.ftr.')->group(function () {
                Route::get('import', [HungerStationFtrImportController::class, 'showUploadForm'])
                    ->name('import');
                Route::post('import/upload', [HungerStationFtrImportController::class, 'upload'])
                    ->name('import.upload');
                Route::get('import/preview', [HungerStationFtrImportController::class, 'preview'])
                    ->name('import.preview');
                Route::post('import/confirm', [HungerStationFtrImportController::class, 'confirm'])
                    ->name('import.confirm');
                Route::post('import/cancel', [HungerStationFtrImportController::class, 'cancel'])
                    ->name('import.cancel');
                Route::post('import/delete', [HungerStationFtrImportController::class, 'deleteImport'])
                    ->name('import.delete');
                Route::get('import/history', [HungerStationFtrImportController::class, 'history'])
                    ->name('import.history');
            });

            // Chefz import pipeline (replace-by-payout semantics — one batch per payout per period)
            Route::prefix('chefz')->name('chefz.')->group(function () {
                Route::get('import', [ChefzImportController::class, 'showUploadForm'])
                    ->name('import');
                Route::post('import/upload', [ChefzImportController::class, 'upload'])
                    ->name('import.upload');
                Route::get('import/preview', [ChefzImportController::class, 'preview'])
                    ->name('import.preview');
                Route::post('import/confirm', [ChefzImportController::class, 'confirm'])
                    ->name('import.confirm');
                Route::post('import/cancel', [ChefzImportController::class, 'cancel'])
                    ->name('import.cancel');
                Route::get('import/history', [ChefzImportController::class, 'history'])
                    ->name('import.history');
            });

            // Chefz settlement workspace
            Route::prefix('chefz/settlement')->name('chefz.settlement.')->group(function () {
                Route::get('/', [ChefzSettlementController::class, 'index'])
                    ->name('index');
                Route::get('/{settlement}', [ChefzSettlementController::class, 'show'])
                    ->name('show');
            });

            // HungerStation FTR settlement workspace
            Route::prefix('hungerstation/ftr/settlement')->name('hungerstation.ftr.settlement.')->group(function () {
                Route::get('/', [HungerStationFtrSettlementController::class, 'index'])
                    ->name('index');
                Route::get('/{settlement}', [HungerStationFtrSettlementController::class, 'show'])
                    ->name('show');

                // Company Adjustments (benefits + deductions unified)
                Route::post('/{settlement}/adjustments', [HungerStationFtrSettlementController::class, 'storeAdjustment'])
                    ->name('adjustments.store');
                Route::put('/{settlement}/adjustments/{deduction}', [HungerStationFtrSettlementController::class, 'updateAdjustment'])
                    ->name('adjustments.update');
                Route::delete('/{settlement}/adjustments/{deduction}', [HungerStationFtrSettlementController::class, 'destroyAdjustment'])
                    ->name('adjustments.destroy');

                // Pending financial entries → settlement import (Batch 5)
                Route::post('/pending-entries/apply', [PendingEntryImportController::class, 'apply'])
                    ->name('pending-entries.apply');

                // Legacy aliases — keep old URLs functional (bookmarks, cached links)
                Route::post('/{settlement}/deductions', [HungerStationFtrSettlementController::class, 'storeDeduction'])
                    ->name('deductions.store');
                Route::delete('/{settlement}/deductions/{deduction}', [HungerStationFtrSettlementController::class, 'destroyDeduction'])
                    ->name('deductions.destroy');
            });
        });
    });
