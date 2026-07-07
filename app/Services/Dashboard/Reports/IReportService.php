<?php
namespace App\Services\Dashboard\Reports;

interface IReportService
{
    public function generateMonthlyPaymentReport(int $periodId);
    public function generateDelegateHistoryReport(int $delegateId);
    public function generatePlatformSummaryReport(int $periodId);
    public function exportToExcel(array $reportData);
}
