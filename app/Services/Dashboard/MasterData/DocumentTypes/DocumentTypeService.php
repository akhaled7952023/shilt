<?php

namespace App\Services\Dashboard\MasterData\DocumentTypes;

use App\Repositories\Dashboard\MasterData\DocumentTypes\IDocumentTypeRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DocumentTypeService implements IDocumentTypeService
{
    public function __construct(
        private IDocumentTypeRepository $repository,
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
        $documentType = $this->repository->getById((int) $id);

        if ($documentType === null) {
            throw new ModelNotFoundException('نوع الوثيقة غير موجود');
        }

        return $documentType;
    }

    public function getForDelegates(): Collection
    {
        return $this->repository->getForDelegates();
    }

    public function getForVehicles(): Collection
    {
        return $this->repository->getForVehicles();
    }

    public function create(array $data)
    {
        $documentType = $this->repository->create($data);

        $this->auditService->log('created', $documentType, [], $documentType->getAttributes());

        return $documentType;
    }

    public function update($id, array $data)
    {
        $documentType = $this->getById($id);
        $old          = $documentType->getAttributes();

        $this->repository->update($documentType, $data);

        $this->auditService->log('updated', $documentType->fresh(), $old, $documentType->fresh()->getAttributes());

        return $documentType->fresh();
    }

    public function delete($id): void
    {
        $documentType = $this->getById($id);

        if ($this->repository->isReferenced((int) $id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن الحذف: نوع الوثيقة مرتبط بوثائق.',
            ]);
        }

        $this->auditService->log('deleted', $documentType, $documentType->getAttributes(), []);

        $this->repository->delete($documentType);
    }

    public function toggleActive($id)
    {
        $documentType = $this->getById($id);
        $oldState     = $documentType->is_active;

        $this->repository->toggleActive($documentType);

        $this->auditService->log(
            'updated',
            $documentType->fresh(),
            ['is_active' => $oldState],
            ['is_active' => !$oldState]
        );

        return $documentType->fresh();
    }
}
