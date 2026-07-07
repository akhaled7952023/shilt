<?php

namespace App\Repositories\Dashboard\VehicleAssignments;

use App\Models\VehicleAssignment;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleAssignmentRepository
{
    public function getActive(int $vehicleId): ?VehicleAssignment;
    public function getForVehicle(int $vehicleId): Collection;
    public function getForDelegate(int $delegateId): Collection;
    public function create(array $data): VehicleAssignment;
    public function update(VehicleAssignment $assignment, array $data): bool;
}
