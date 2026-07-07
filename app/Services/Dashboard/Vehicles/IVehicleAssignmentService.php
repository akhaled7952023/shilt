<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleAssignment;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleAssignmentService
{
    public function assign(int $vehicleId, array $data): VehicleAssignment;
    public function unassign(int $vehicleId): void;
    public function returnVehicle(int $assignmentId, array $data): VehicleAssignment;
    public function getActive(int $vehicleId): ?VehicleAssignment;
    public function getForVehicle(int $vehicleId): Collection;
    public function getForDelegate(int $delegateId): Collection;
}
