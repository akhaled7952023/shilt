<?php

namespace App\Repositories\Dashboard\Vehicles;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IVehicleRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function getAvailable(): Collection;

    public function getById(int $id): ?Vehicle;

    public function getWithRelations(int $id): ?Vehicle;

    public function findByPlate(string $plate): ?Vehicle;

    public function hasActiveAssignment(int $vehicleId): bool;

    public function hasOperationalHistory(int $vehicleId): bool;

    public function create(array $data): Vehicle;

    public function update(Vehicle $vehicle, array $data): bool;

    public function delete(Vehicle $vehicle): bool;
}
