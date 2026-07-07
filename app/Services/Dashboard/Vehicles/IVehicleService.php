<?php

namespace App\Services\Dashboard\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface IVehicleService
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function getById(int $id): Vehicle;

    public function getAvailable(): Collection;

    public function getWithRelations(int $id): Vehicle;

    public function create(
        array $data,
        ?UploadedFile $vehicleImage = null,
        ?UploadedFile $registrationImage = null,
        ?UploadedFile $insuranceImage = null
    ): Vehicle;

    public function update(
        int $id,
        array $data,
        ?UploadedFile $vehicleImage = null,
        ?UploadedFile $registrationImage = null,
        ?UploadedFile $insuranceImage = null
    ): Vehicle;

    public function delete(int $id): void;

    public function updateStatus(int $id, VehicleStatus $status): Vehicle;
}
