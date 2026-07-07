<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleViolation;
use App\Repositories\Dashboard\Vehicles\IVehicleViolationRepository;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class VehicleViolationService implements IVehicleViolationService
{
    public function __construct(
        private IVehicleViolationRepository $repository,
        private AuditService $auditService,
    ) {}

    public function getForVehicle(int $vehicleId): Collection
    {
        return $this->repository->getForVehicle($vehicleId);
    }

    public function getById(int $id): VehicleViolation
    {
        $record = $this->repository->getById($id);
        if ($record === null) {
            throw new ModelNotFoundException('المخالفة غير موجودة');
        }
        return $record;
    }

    public function create(int $vehicleId, array $data): VehicleViolation
    {
        return DB::transaction(function () use ($vehicleId, $data) {
            $data['vehicle_id'] = $vehicleId;
            $record = $this->repository->create($data);
            $this->auditService->log('created', $record, [], $record->getAttributes());
            return $record;
        });
    }

    public function update(int $id, array $data): VehicleViolation
    {
        $record = $this->getById($id);
        $old    = $record->getAttributes();

        return DB::transaction(function () use ($record, $data, $old) {
            $this->repository->update($record, $data);
            $record->refresh();
            $this->auditService->log('updated', $record, $old, $record->getAttributes());
            return $record;
        });
    }

    public function delete(int $id): void
    {
        $record = $this->getById($id);

        DB::transaction(function () use ($record) {
            $this->auditService->log('deleted', $record, $record->getAttributes(), []);
            $this->repository->delete($record);
        });
    }
}
