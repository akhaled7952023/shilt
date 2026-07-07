<?php
namespace App\Services\Dashboard\Monthly;

interface ICompanyDeductionService
{
    public function getForEntry(int $entryId);
    public function syncDeductions(int $entryId, array $deductions);
}
