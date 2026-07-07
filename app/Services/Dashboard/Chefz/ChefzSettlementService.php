<?php

namespace App\Services\Dashboard\Chefz;

use App\Models\ChefzDelegateSettlement;

class ChefzSettlementService
{
    /**
     * Chefz settlements are fully automatic — status reflects import completeness only.
     */
    public function settlementStatus(ChefzDelegateSettlement $settlement): string
    {
        if ($settlement->is_locked) {
            return 'locked';
        }

        if ($settlement->total_orders == 0) {
            return 'incomplete';
        }

        return 'calculated';
    }
}
