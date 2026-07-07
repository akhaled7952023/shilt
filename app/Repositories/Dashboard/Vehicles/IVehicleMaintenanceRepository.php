<?php

namespace App\Repositories\Dashboard\Vehicles;

use App\Models\VehicleMaintenance;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleMaintenanceRepository
{
    public function getForVehicle(int $vehicleId): Collection;
    public function getById(int $id): ?VehicleMaintenance;
    public function create(array $data): VehicleMaintenance;
    public function update(VehicleMaintenance $maintenance, array $data): bool;
    public function delete(VehicleMaintenance $maintenance): bool;
}
