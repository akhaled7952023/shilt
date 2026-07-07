<?php

namespace App\Repositories\Dashboard\Vehicles;

use App\Models\VehicleViolation;
use Illuminate\Database\Eloquent\Collection;

class VehicleViolationRepository implements IVehicleViolationRepository
{
    public function getForVehicle(int $vehicleId): Collection
    {
        return VehicleViolation::with(['delegate', 'warningType'])
            ->where('vehicle_id', $vehicleId)
            ->orderByDesc('date')
            ->get();
    }

    public function getById(int $id): ?VehicleViolation
    {
        return VehicleViolation::with(['delegate', 'warningType'])->find($id);
    }

    public function create(array $data): VehicleViolation
    {
        return VehicleViolation::create($data);
    }

    public function update(VehicleViolation $violation, array $data): bool
    {
        return $violation->update($data);
    }

    public function delete(VehicleViolation $violation): bool
    {
        return (bool) $violation->delete();
    }
}
