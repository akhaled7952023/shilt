<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleMaintenance;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleMaintenanceService
{
    public function getForVehicle(int $vehicleId): Collection;
    public function getById(int $id): VehicleMaintenance;
    public function create(int $vehicleId, array $data): VehicleMaintenance;
    public function update(int $id, array $data): VehicleMaintenance;
    public function delete(int $id): void;
}
