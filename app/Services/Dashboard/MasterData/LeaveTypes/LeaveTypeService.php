<?php

namespace App\Services\Dashboard\MasterData\LeaveTypes;

use App\Repositories\Dashboard\MasterData\LeaveTypes\ILeaveTypeRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaveTypeService implements ILeaveTypeService
{
    public function __construct(
        private ILeaveTypeRepository $repository,
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
        $leaveType = $this->repository->getById((int) $id);

        if ($leaveType === null) {
            throw new ModelNotFoundException('نوع الإجازة غير موجود');
        }

        return $leaveType;
    }

    public function create(array $data)
    {
        $leaveType = $this->repository->create($data);

        $this->auditService->log('created', $leaveType, [], $leaveType->getAttributes());

        return $leaveType;
    }

    public function update($id, array $data)
    {
        $leaveType = $this->getById($id);
        $old       = $leaveType->getAttributes();

        $this->repository->update($leaveType, $data);

        $this->auditService->log('updated', $leaveType->fresh(), $old, $leaveType->fresh()->getAttributes());

        return $leaveType->fresh();
    }

    public function delete($id): void
    {
        $leaveType = $this->getById($id);

        if ($this->repository->isReferenced((int) $id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن الحذف: نوع الإجازة مرتبط بإدخالات.',
            ]);
        }

        $this->auditService->log('deleted', $leaveType, $leaveType->getAttributes(), []);

        $this->repository->delete($leaveType);
    }

    public function toggleActive($id)
    {
        $leaveType = $this->getById($id);
        $oldState  = $leaveType->is_active;

        $this->repository->toggleActive($leaveType);

        $this->auditService->log(
            'updated',
            $leaveType->fresh(),
            ['is_active' => $oldState],
            ['is_active' => !$oldState]
        );

        return $leaveType->fresh();
    }
}
