<?php

namespace App\Repositories\Dashboard\Delegates;

use App\Models\LeaveEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DelegateLeaveRepository implements IDelegateLeaveRepository
{
    public function getForDelegate(int $delegateId, int $perPage): LengthAwarePaginator
    {
        return LeaveEntry::with('leaveType')
            ->where('delegate_id', $delegateId)
            ->orderByDesc('start_date')
            ->paginate($perPage);
    }

    public function getById(int $id): ?LeaveEntry
    {
        return LeaveEntry::with('leaveType')->find($id);
    }

    public function create(array $data): LeaveEntry
    {
        return LeaveEntry::create($data);
    }

    public function update(LeaveEntry $leave, array $data): bool
    {
        return $leave->update($data);
    }

    public function delete(LeaveEntry $leave): bool
    {
        return (bool) $leave->delete();
    }
}
