<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleViolation;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleViolationService
{
    public function getForVehicle(int $vehicleId): Collection;
    public function getById(int $id): VehicleViolation;
    public function create(int $vehicleId, array $data): VehicleViolation;
    public function update(int $id, array $data): VehicleViolation;
    public function delete(int $id): void;
}
