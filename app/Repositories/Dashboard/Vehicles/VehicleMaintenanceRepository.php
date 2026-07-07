<?php

namespace App\Repositories\Dashboard\Vehicles;

use App\Models\VehicleMaintenance;
use Illuminate\Database\Eloquent\Collection;

class VehicleMaintenanceRepository implements IVehicleMaintenanceRepository
{
    public function getForVehicle(int $vehicleId): Collection
    {
        return VehicleMaintenance::where('vehicle_id', $vehicleId)
            ->orderByDesc('date')
            ->get();
    }

    public function getById(int $id): ?VehicleMaintenance
    {
        return VehicleMaintenance::find($id);
    }

    public function create(array $data): VehicleMaintenance
    {
        return VehicleMaintenance::create($data);
    }

    public function update(VehicleMaintenance $maintenance, array $data): bool
    {
        return $maintenance->update($data);
    }

    public function delete(VehicleMaintenance $maintenance): bool
    {
        return (bool) $maintenance->delete();
    }
}
