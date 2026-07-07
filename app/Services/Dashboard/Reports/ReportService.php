<?php
namespace App\Services\Dashboard\Reports;

class ReportService implements IReportService
{
    public function generateMonthlyPaymentReport(int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function generateDelegateHistoryReport(int $delegateId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function generatePlatformSummaryReport(int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function exportToExcel(array $reportData)
    {
        throw new \RuntimeException('Not implemented');
    }
}
