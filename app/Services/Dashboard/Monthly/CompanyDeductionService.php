<?php
namespace App\Services\Dashboard\Monthly;

class CompanyDeductionService implements ICompanyDeductionService
{
    public function getForEntry(int $entryId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function syncDeductions(int $entryId, array $deductions)
    {
        throw new \RuntimeException('Not implemented');
    }
}
