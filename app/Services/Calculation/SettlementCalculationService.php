<?php
namespace App\Services\Calculation;

use App\DTOs\SettlementResult;
use App\Models\DelegateMonthlyEntry;

class SettlementCalculationService implements ISettlementCalculationService
{
    public function calculate(DelegateMonthlyEntry $entry): SettlementResult
    {
        throw new \RuntimeException('Not implemented — deferred to Phase 3.');
    }

    public function persist(DelegateMonthlyEntry $entry, SettlementResult $result): void
    {
        throw new \RuntimeException('Not implemented — deferred to Phase 3.');
    }

    public function recalculateAll(int $periodId): void
    {
        throw new \RuntimeException('Not implemented — deferred to Phase 3.');
    }
}
