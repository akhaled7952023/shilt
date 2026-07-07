<?php

namespace App\Policies;

use App\Models\MonthlyPeriod;
use App\Models\User;

class MonthlyPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAccess('monthly-periods');
    }

    public function view(User $user, MonthlyPeriod $period): bool
    {
        return $user->hasAccess('monthly-periods');
    }

    public function create(User $user): bool
    {
        return $user->hasAccess('monthly-periods');
    }

    public function close(User $user, MonthlyPeriod $period): bool
    {
        return $user->isSuperAdmin();
    }

    public function reopen(User $user, MonthlyPeriod $period): bool
    {
        return $user->isSuperAdmin();
    }
}
