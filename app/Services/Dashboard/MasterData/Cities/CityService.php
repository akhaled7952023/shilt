<?php

namespace App\Services\Dashboard\MasterData\Cities;

use App\Repositories\Dashboard\MasterData\Cities\ICityRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CityService implements ICityService
{
    public function __construct(
        private ICityRepository $repository,
        private AuditService $auditService
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
        $city = $this->repository->getById((int) $id);

        if ($city === null) {
            throw new ModelNotFoundException('المدينة غير موجودة');
        }

        return $city;
    }

    public function create(array $data)
    {
        $city = $this->repository->create($data);

        $this->auditService->log('created', $city, [], $city->getAttributes());

        return $city;
    }

    public function update($id, array $data)
    {
        $city = $this->getById($id);
        $old  = $city->getAttributes();

        $this->repository->update($city, $data);

        $this->auditService->log('updated', $city->fresh(), $old, $city->fresh()->getAttributes());

        return $city->fresh();
    }

    public function delete($id): void
    {
        $city = $this->getById($id);

        if ($this->repository->isReferenced((int) $id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن الحذف: المدينة مرتبطة بمناديب.',
            ]);
        }

        $this->auditService->log('deleted', $city, $city->getAttributes(), []);

        $this->repository->delete($city);
    }

    public function toggleActive($id)
    {
        $city    = $this->getById($id);
        $oldState = $city->is_active;

        $this->repository->toggleActive($city);

        $this->auditService->log(
            'updated',
            $city->fresh(),
            ['is_active' => $oldState],
            ['is_active' => !$oldState]
        );

        return $city->fresh();
    }
}
