<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleRental;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IVehicleRentalService
{
    public function getForVehicle(int $vehicleId): LengthAwarePaginator;
    public function getForPeriod(int $periodId): Collection;
    public function create(int $vehicleId, array $data): VehicleRental;
    public function update(int $rentalId, array $data): VehicleRental;
    public function delete(int $rentalId): void;
}
