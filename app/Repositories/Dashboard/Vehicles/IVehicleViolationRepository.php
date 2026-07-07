<?php

namespace App\Repositories\Dashboard\Vehicles;

use App\Models\VehicleViolation;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleViolationRepository
{
    public function getForVehicle(int $vehicleId): Collection;
    public function getById(int $id): ?VehicleViolation;
    public function create(array $data): VehicleViolation;
    public function update(VehicleViolation $violation, array $data): bool;
    public function delete(VehicleViolation $violation): bool;
}
