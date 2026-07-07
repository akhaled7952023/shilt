<?php

namespace App\Repositories\Dashboard\MasterData\VehicleTypes;

use App\Models\VehicleType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IVehicleTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getById(int $id): ?VehicleType;
    public function isReferenced(int $id): bool;
    public function create(array $data): VehicleType;
    public function update(VehicleType $vehicleType, array $data): bool;
    public function delete(VehicleType $vehicleType): bool;
    public function toggleActive(VehicleType $vehicleType): bool;
}
