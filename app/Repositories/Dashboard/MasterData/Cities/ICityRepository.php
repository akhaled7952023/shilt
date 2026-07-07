<?php

namespace App\Repositories\Dashboard\MasterData\Cities;

use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ICityRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getById(int $id): ?City;
    public function isReferenced(int $id): bool;
    public function create(array $data): City;
    public function update(City $city, array $data): bool;
    public function delete(City $city): bool;
    public function toggleActive(City $city): bool;
}
