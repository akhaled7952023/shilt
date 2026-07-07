<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAccess('vehicles');
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->hasAccess('vehicles');
    }

    public function create(User $user): bool
    {
        return $user->hasAccess('vehicles');
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->hasAccess('vehicles');
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->hasAccess('vehicles');
    }
}
