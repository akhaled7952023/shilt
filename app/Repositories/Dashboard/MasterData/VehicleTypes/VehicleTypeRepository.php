<?php

namespace App\Repositories\Dashboard\MasterData\VehicleTypes;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VehicleTypeRepository implements IVehicleTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = VehicleType::query();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name->ar', 'like', "%{$term}%")
                  ->orWhere('name->en', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function getAllActive(): Collection
    {
        return VehicleType::where('is_active', true)->orderBy('id')->get();
    }

    public function getById(int $id): ?VehicleType
    {
        return VehicleType::find($id);
    }

    public function isReferenced(int $id): bool
    {
        return Vehicle::where('vehicle_type_id', $id)->exists();
    }

    public function create(array $data): VehicleType
    {
        return VehicleType::create($data);
    }

    public function update(VehicleType $vehicleType, array $data): bool
    {
        return $vehicleType->update($data);
    }

    public function delete(VehicleType $vehicleType): bool
    {
        return (bool) $vehicleType->delete();
    }

    public function toggleActive(VehicleType $vehicleType): bool
    {
        return $vehicleType->update(['is_active' => !$vehicleType->is_active]);
    }
}
