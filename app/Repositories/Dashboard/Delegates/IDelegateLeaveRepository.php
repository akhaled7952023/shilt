<?php

namespace App\Repositories\Dashboard\Delegates;

use App\Models\LeaveEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IDelegateLeaveRepository
{
    public function getForDelegate(int $delegateId, int $perPage): LengthAwarePaginator;

    public function getById(int $id): ?LeaveEntry;

    public function create(array $data): LeaveEntry;

    public function update(LeaveEntry $leave, array $data): bool;

    public function delete(LeaveEntry $leave): bool;
}
