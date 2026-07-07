<?php

namespace App\DTOs;

readonly class SettlementResult
{
    public function __construct(
        public float $shortDistancePenalty,
        public float $grossEntitlement,
        public float $totalDeductions,
        public float $netSettlement,
        public bool  $isNegative,
    ) {}
}
