<?php

namespace App\Repositories\Dashboard\MasterData\WarningTypes;

use App\Models\WarningType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IWarningTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getById(int $id): ?WarningType;
    public function isReferenced(int $id): bool;
    public function create(array $data): WarningType;
    public function update(WarningType $warningType, array $data): bool;
    public function delete(WarningType $warningType): bool;
    public function toggleActive(WarningType $warningType): bool;
}
