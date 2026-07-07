<?php

namespace App\Services\Dashboard\MasterData\VehicleTypes;

use App\Repositories\Dashboard\MasterData\VehicleTypes\IVehicleTypeRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class VehicleTypeService implements IVehicleTypeService
{
    public function __construct(
        private IVehicleTypeRepository $repository,
        private AuditService $auditService
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAll($filters);
    }

    public function getAllActive(): Collection
    {
        return $this->repository->getAllActive();
    }

    public function getById($id)
    {
        $vehicleType = $this->repository->getById((int) $id);

        if ($vehicleType === null) {
            throw new ModelNotFoundException('نوع المركبة غير موجود');
        }

        return $vehicleType;
    }

    public function create(array $data)
    {
        $vehicleType = $this->repository->create($data);

        $this->auditService->log('created', $vehicleType, [], $vehicleType->getAttributes());

        return $vehicleType;
    }

    public function update($id, array $data)
    {
        $vehicleType = $this->getById($id);
        $old         = $vehicleType->getAttributes();

        $this->repository->update($vehicleType, $data);

        $this->auditService->log('updated', $vehicleType->fresh(), $old, $vehicleType->fresh()->getAttributes());

        return $vehicleType->fresh();
    }

    public function delete($id): void
    {
        $vehicleType = $this->getById($id);

        if ($this->repository->isReferenced((int) $id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن الحذف: نوع المركبة مرتبط بمركبات.',
            ]);
        }

        $this->auditService->log('deleted', $vehicleType, $vehicleType->getAttributes(), []);

        $this->repository->delete($vehicleType);
    }

    public function toggleActive($id)
    {
        $vehicleType = $this->getById($id);
        $oldState    = $vehicleType->is_active;

        $this->repository->toggleActive($vehicleType);

        $this->auditService->log(
            'updated',
            $vehicleType->fresh(),
            ['is_active' => $oldState],
            ['is_active' => !$oldState]
        );

        return $vehicleType->fresh();
    }
}
