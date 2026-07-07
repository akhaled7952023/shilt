<?php

namespace App\Services\Dashboard\Delegates;

use App\Models\LeaveEntry;
use App\Repositories\Dashboard\Delegates\IDelegateLeaveRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DelegateLeaveService implements IDelegateLeaveService
{
    public function __construct(
        private IDelegateLeaveRepository $leaveRepository,
        private AuditService             $auditService,
    ) {}

    public function getForDelegate(int $delegateId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->leaveRepository->getForDelegate($delegateId, $perPage);
    }

    public function getById(int $id): LeaveEntry
    {
        $leave = $this->leaveRepository->getById($id);
        if ($leave === null) {
            throw new ModelNotFoundException('سجل الإجازة غير موجود');
        }
        return $leave;
    }

    public function create(int $delegateId, array $data): LeaveEntry
    {
        $data['delegate_id'] = $delegateId;

        return DB::transaction(function () use ($data) {
            $leave = $this->leaveRepository->create($data);
            $this->auditService->log('created', $leave, [], $leave->getAttributes());
            return $leave;
        });
    }

    public function update(int $id, array $data): LeaveEntry
    {
        $leave     = $this->getById($id);
        $oldValues = $leave->getAttributes();

        return DB::transaction(function () use ($leave, $data, $oldValues) {
            $this->leaveRepository->update($leave, $data);
            $leave->refresh();
            $this->auditService->log('updated', $leave, $oldValues, $leave->getAttributes());
            return $leave;
        });
    }

    public function delete(int $id): void
    {
        $leave = $this->getById($id);

        DB::transaction(function () use ($leave) {
            $this->auditService->log('deleted', $leave, $leave->getAttributes(), []);
            $this->leaveRepository->delete($leave);
        });
    }
}
