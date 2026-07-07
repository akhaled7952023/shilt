<?php

namespace App\Services\Dashboard\Delegates;

use App\Models\LeaveEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IDelegateLeaveService
{
    public function getForDelegate(int $delegateId, int $perPage = 10): LengthAwarePaginator;

    public function getById(int $id): LeaveEntry;

    public function create(int $delegateId, array $data): LeaveEntry;

    public function update(int $id, array $data): LeaveEntry;

    public function delete(int $id): void;
}
