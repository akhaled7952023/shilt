<?php

namespace App\Services\Dashboard\Monthly;

use App\Models\MonthlyPeriod;
use App\Repositories\Dashboard\Monthly\IMonthlyPeriodRepository;
use App\Services\Dashboard\Monthly\MonthCloseService;
use Illuminate\Database\Eloquent\Collection;

class MonthlyPeriodService implements IMonthlyPeriodService
{
    public function __construct(
        protected IMonthlyPeriodRepository $repository,
        protected MonthCloseService $closeService,
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): ?MonthlyPeriod
    {
        return $this->repository->getById($id);
    }

    public function create(array $data): MonthlyPeriod
    {
        $data['label'] = MonthlyPeriod::makeLabel((int) $data['month'], (int) $data['year']);
        $data['status'] = 'open';

        return $this->repository->create($data);
    }

    public function getCurrent(): ?MonthlyPeriod
    {
        return MonthlyPeriod::current();
    }

    public function getPublishedAndClosed(): Collection
    {
        return MonthlyPeriod::closed()->with('platform')->orderByDesc('year')->orderByDesc('month')->get();
    }

    public function close(int $id, int $closedByUserId): void
    {
        $period = $this->repository->getById($id);

        if (! $period) {
            throw new \RuntimeException('الفترة غير موجودة.');
        }

        $this->closeService->close($period, $closedByUserId);
    }

    public function reopen(int $id): void
    {
        $period = $this->repository->getById($id);

        if (! $period) {
            throw new \RuntimeException('الفترة غير موجودة.');
        }

        $this->closeService->reopen($period);
    }
}
