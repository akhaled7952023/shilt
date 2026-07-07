<?php

namespace App\Policies;

use App\Enums\PeriodStatus;
use App\Models\DelegateMonthlyEntry;
use App\Models\User;

class DelegateMonthlyEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAccess('monthly-periods');
    }

    public function view(User $user, DelegateMonthlyEntry $entry): bool
    {
        return $user->hasAccess('monthly-periods');
    }

    public function createOrUpdate(User $user, DelegateMonthlyEntry $entry): bool
    {
        return $user->hasAccess('monthly-periods') && in_array($entry->monthlyPeriod->status, [PeriodStatus::Open, PeriodStatus::Editing]);
    }
}
