<?php

namespace App\Repositories\Dashboard\Monthly;

use App\Models\MonthlyPeriod;
use Illuminate\Database\Eloquent\Collection;

interface IMonthlyPeriodRepository
{
    public function getAll(array $filters = []): Collection;

    public function getById(int $id): ?MonthlyPeriod;

    public function create(array $data): MonthlyPeriod;

    public function update(MonthlyPeriod $model, array $data): MonthlyPeriod;

    public function delete(MonthlyPeriod $model): void;

    public function findByYearMonth(int $year, int $month): ?MonthlyPeriod;

    public function findByPlatformYearMonth(int $platformId, int $year, int $month): ?MonthlyPeriod;
}
