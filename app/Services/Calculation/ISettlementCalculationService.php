<?php
namespace App\Services\Calculation;

use App\DTOs\SettlementResult;
use App\Models\DelegateMonthlyEntry;

interface ISettlementCalculationService
{
    public function calculate(DelegateMonthlyEntry $entry): SettlementResult;
    public function persist(DelegateMonthlyEntry $entry, SettlementResult $result): void;
    public function recalculateAll(int $periodId): void;
}
