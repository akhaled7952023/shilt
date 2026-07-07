<?php

namespace App\Repositories\Dashboard\Monthly;

use App\Models\MonthlyPeriod;
use Illuminate\Database\Eloquent\Collection;

class MonthlyPeriodRepository implements IMonthlyPeriodRepository
{
    public function getAll(array $filters = []): Collection
    {
        return MonthlyPeriod::with('platform')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    public function getById(int $id): ?MonthlyPeriod
    {
        return MonthlyPeriod::with('platform')->find($id);
    }

    public function create(array $data): MonthlyPeriod
    {
        return MonthlyPeriod::create($data);
    }

    public function update(MonthlyPeriod $model, array $data): MonthlyPeriod
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(MonthlyPeriod $model): void
    {
        $model->delete();
    }

    public function findByYearMonth(int $year, int $month): ?MonthlyPeriod
    {
        return MonthlyPeriod::where('year', $year)->where('month', $month)->first();
    }

    public function findByPlatformYearMonth(int $platformId, int $year, int $month): ?MonthlyPeriod
    {
        return MonthlyPeriod::where('platform_id', $platformId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }
}
