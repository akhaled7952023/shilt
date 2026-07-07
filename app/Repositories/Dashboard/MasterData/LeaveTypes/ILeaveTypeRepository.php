<?php

namespace App\Repositories\Dashboard\MasterData\LeaveTypes;

use App\Models\LeaveType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ILeaveTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getById(int $id): ?LeaveType;
    public function isReferenced(int $id): bool;
    public function create(array $data): LeaveType;
    public function update(LeaveType $leaveType, array $data): bool;
    public function delete(LeaveType $leaveType): bool;
    public function toggleActive(LeaveType $leaveType): bool;
}
