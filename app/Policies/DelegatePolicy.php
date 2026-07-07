<?php

namespace App\Policies;

use App\Models\Delegate;
use App\Models\User;

class DelegatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAccess('delegates');
    }

    public function view(User $user, Delegate $delegate): bool
    {
        return $user->hasAccess('delegates');
    }

    public function create(User $user): bool
    {
        return $user->hasAccess('delegates');
    }

    public function update(User $user, Delegate $delegate): bool
    {
        return $user->hasAccess('delegates');
    }

    public function delete(User $user, Delegate $delegate): bool
    {
        return $user->hasAccess('delegates');
    }

    public function updateStatus(User $user, Delegate $delegate): bool
    {
        return $user->hasAccess('delegates');
    }
}
