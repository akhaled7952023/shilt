<?php

namespace App\Repositories\Dashboard\MasterData\Platforms;

use App\Models\Platform;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IPlatformRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getById(int $id): ?Platform;
    public function getByCode(string $code): ?Platform;
    public function isReferenced(int $id): bool;
    public function create(array $data): Platform;
    public function update(Platform $platform, array $data): bool;
    public function delete(Platform $platform): bool;
    public function toggleActive(Platform $platform): bool;
}
