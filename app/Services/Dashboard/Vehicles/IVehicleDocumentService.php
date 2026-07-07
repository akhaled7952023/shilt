<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleDocument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface IVehicleDocumentService
{
    public function getForVehicle(int $vehicleId): Collection;
    public function getExpiryStatus(VehicleDocument $document): string;
    public function store(int $vehicleId, array $data, UploadedFile $file): VehicleDocument;
    public function delete(int $documentId): void;
    public function getExpiring(int $days): Collection;
    public function getExpired(): Collection;
}
