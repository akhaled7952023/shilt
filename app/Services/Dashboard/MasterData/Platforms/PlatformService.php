<?php

namespace App\Services\Dashboard\MasterData\Platforms;

use App\Repositories\Dashboard\MasterData\Platforms\IPlatformRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class PlatformService implements IPlatformService
{
    public function __construct(
        private IPlatformRepository $repository
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAll($filters);
    }

    public function getAllActive(): Collection
    {
        return $this->repository->getAllActive();
    }

    public function getById($id)
    {
        $platform = $this->repository->getById((int) $id);

        if ($platform === null) {
            throw new ModelNotFoundException('المنصة غير موجودة');
        }

        return $platform;
    }

    public function getActive(): Collection
    {
        return $this->repository->getAllActive();
    }

    public function getByCode(string $code)
    {
        return $this->repository->getByCode($code);
    }

    public function getWithSettings(int $id)
    {
        return $this->getById($id);
    }
}
