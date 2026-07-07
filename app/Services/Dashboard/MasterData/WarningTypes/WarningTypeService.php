<?php

namespace App\Services\Dashboard\MasterData\WarningTypes;

use App\Repositories\Dashboard\MasterData\WarningTypes\IWarningTypeRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WarningTypeService implements IWarningTypeService
{
    public function __construct(
        private IWarningTypeRepository $repository,
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
        $warningType = $this->repository->getById((int) $id);

        if ($warningType === null) {
            throw new ModelNotFoundException('نوع الإنذار غير موجود');
        }

        return $warningType;
    }

    public function create(array $data)
    {
        $warningType = $this->repository->create($data);

        $this->auditService->log('created', $warningType, [], $warningType->getAttributes());

        return $warningType;
    }

    public function update($id, array $data)
    {
        $warningType = $this->getById($id);
        $old         = $warningType->getAttributes();

        $this->repository->update($warningType, $data);

        $this->auditService->log('updated', $warningType->fresh(), $old, $warningType->fresh()->getAttributes());

        return $warningType->fresh();
    }

    public function delete($id): void
    {
        $warningType = $this->getById($id);

        if ($this->repository->isReferenced((int) $id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن الحذف: نوع الإنذار مرتبط بإدخالات.',
            ]);
        }

        $this->auditService->log('deleted', $warningType, $warningType->getAttributes(), []);

        $this->repository->delete($warningType);
    }

    public function toggleActive($id)
    {
        $warningType = $this->getById($id);
        $oldState    = $warningType->is_active;

        $this->repository->toggleActive($warningType);

        $this->auditService->log(
            'updated',
            $warningType->fresh(),
            ['is_active' => $oldState],
            ['is_active' => !$oldState]
        );

        return $warningType->fresh();
    }
}
