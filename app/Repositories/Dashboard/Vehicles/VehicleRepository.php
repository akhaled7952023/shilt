<?php

namespace App\Repositories\Dashboard\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleMaintenance;
use App\Models\VehicleViolation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VehicleRepository implements IVehicleRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Vehicle::with(['vehicleType', 'activeAssignment.delegate'])
            ->orderBy('plate_number');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['vehicle_type_id'])) {
            $query->where('vehicle_type_id', $filters['vehicle_type_id']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('plate_number', 'like', "%{$term}%")
                  ->orWhere('make', 'like', "%{$term}%")
                  ->orWhere('model', 'like', "%{$term}%")
                  ->orWhereHas('activeAssignment.delegate', function ($dq) use ($term) {
                      $dq->where('name', 'like', "%{$term}%");
                  });
            });
        }

        return $query->paginate(25);
    }

    public function getAvailable(): Collection
    {
        return Vehicle::where('status', VehicleStatus::Available)
            ->select(['id', 'plate_number', 'make', 'model'])
            ->orderBy('plate_number')
            ->get();
    }

    public function getById(int $id): ?Vehicle
    {
        return Vehicle::find($id);
    }

    public function getWithRelations(int $id): ?Vehicle
    {
        return Vehicle::with([
            'vehicleType',
            'activeAssignment.delegate.city',
            'vehicleAssignments' => fn($q) => $q->with('delegate')->orderByDesc('assigned_at'),
            'vehicleMaintenance',
            'vehicleViolations.delegate',
            'vehicleViolations.warningType',
        ])->find($id);
    }

    public function findByPlate(string $plate): ?Vehicle
    {
        return Vehicle::where('plate_number', $plate)->first();
    }

    public function hasActiveAssignment(int $vehicleId): bool
    {
        return VehicleAssignment::where('vehicle_id', $vehicleId)
            ->where('is_active', true)
            ->exists();
    }

    public function hasOperationalHistory(int $vehicleId): bool
    {
        return VehicleAssignment::where('vehicle_id', $vehicleId)->exists()
            || VehicleMaintenance::where('vehicle_id', $vehicleId)->exists()
            || VehicleViolation::where('vehicle_id', $vehicleId)->exists();
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): bool
    {
        return $vehicle->update($data);
    }

    public function delete(Vehicle $vehicle): bool
    {
        return (bool) $vehicle->delete();
    }
}
