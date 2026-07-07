<?php

namespace App\Repositories\Dashboard\VehicleAssignments;

use App\Models\VehicleAssignment;
use Illuminate\Database\Eloquent\Collection;

class VehicleAssignmentRepository implements IVehicleAssignmentRepository
{
    public function getActive(int $vehicleId): ?VehicleAssignment
    {
        return VehicleAssignment::where('vehicle_id', $vehicleId)
            ->where('is_active', true)
            ->with(['delegate'])
            ->first();
    }

    public function getForVehicle(int $vehicleId): Collection
    {
        return VehicleAssignment::where('vehicle_id', $vehicleId)
            ->with(['delegate'])
            ->orderByDesc('assigned_at')
            ->get();
    }

    public function getForDelegate(int $delegateId): Collection
    {
        return VehicleAssignment::where('delegate_id', $delegateId)
            ->with(['vehicle.vehicleType'])
            ->orderByDesc('assigned_at')
            ->get();
    }

    public function create(array $data): VehicleAssignment
    {
        return VehicleAssignment::create($data);
    }

    public function update(VehicleAssignment $assignment, array $data): bool
    {
        return $assignment->update($data);
    }
}
