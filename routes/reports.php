<?php

use App\Http\Controllers\Dashboard\BI\BusinessIntelligenceController;
use App\Http\Controllers\Dashboard\Reports\ComparisonController;
use App\Http\Controllers\Dashboard\Reports\ExecutiveDashboardController;
use App\Http\Controllers\Dashboard\Reports\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:reports'])
    ->prefix('dashboard/reports')
    ->name('dashboard.reports.')
    ->group(function () {
        // الأعمال — Business Intelligence
        Route::get('bi', [BusinessIntelligenceController::class, 'index'])->name('bi');
        Route::get('bi/driver/{delegate}', [BusinessIntelligenceController::class, 'driver'])->name('bi.driver');

        // Period Comparison
        Route::get('comparison', [ComparisonController::class, 'index'])->name('comparison');

        // Executive Management Dashboard
        Route::get('executive', [ExecutiveDashboardController::class, 'index'])->name('executive');
        Route::get('executive/pdf', [ExecutiveDashboardController::class, 'pdf'])->name('executive.pdf');

        // New period-based monthly report (Year/Month filter — never uses order dates)
        Route::get('monthly', [ReportController::class, 'monthlyReport'])->name('monthly');

        // Legacy stub — redirects to period-based report
        Route::get('monthly-payment/{periodId}', [ReportController::class, 'monthlyPayment'])
            ->name('monthly-payment');
        Route::get('delegate-history/{delegateId}', [ReportController::class, 'delegateHistory'])
            ->name('delegate-history');
        Route::get('platform-summary/{periodId}', [ReportController::class, 'platformSummary'])
            ->name('platform-summary');
        Route::get('document-expiry', [ReportController::class, 'documentExpiry'])
            ->name('document-expiry');
        Route::get('export/{type}', [ReportController::class, 'exportExcel'])
            ->name('export');
    });
