<?php

namespace App\Repositories\Dashboard\MasterData\LeaveTypes;

use App\Models\LeaveEntry;
use App\Models\LeaveType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LeaveTypeRepository implements ILeaveTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = LeaveType::query();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name->ar', 'like', "%{$term}%")
                  ->orWhere('name->en', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function getAllActive(): Collection
    {
        return LeaveType::where('is_active', true)->orderBy('id')->get();
    }

    public function getById(int $id): ?LeaveType
    {
        return LeaveType::find($id);
    }

    public function isReferenced(int $id): bool
    {
        return LeaveEntry::where('leave_type_id', $id)->exists();
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::create($data);
    }

    public function update(LeaveType $leaveType, array $data): bool
    {
        return $leaveType->update($data);
    }

    public function delete(LeaveType $leaveType): bool
    {
        return (bool) $leaveType->delete();
    }

    public function toggleActive(LeaveType $leaveType): bool
    {
        return $leaveType->update(['is_active' => !$leaveType->is_active]);
    }
}
