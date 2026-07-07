<?php

namespace App\Repositories\Dashboard\Delegates;

use App\Models\Delegate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IDelegateRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function getActive(): Collection;

    public function getById(int $id): ?Delegate;

    public function getWithRelations(int $id): ?Delegate;

    public function findByNationalId(string $nationalId): ?Delegate;

    public function hasActiveVehicleAssignment(int $delegateId): bool;

    public function create(array $data): Delegate;

    public function update(Delegate $delegate, array $data): bool;

    public function delete(Delegate $delegate): bool;
}
