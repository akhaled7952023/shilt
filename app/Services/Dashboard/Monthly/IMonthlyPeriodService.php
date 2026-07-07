<?php

namespace App\Services\Dashboard\Monthly;

use App\Models\MonthlyPeriod;
use Illuminate\Database\Eloquent\Collection;

interface IMonthlyPeriodService
{
    public function getAll(): Collection;

    public function getById(int $id): ?MonthlyPeriod;

    public function create(array $data): MonthlyPeriod;

    public function getCurrent(): ?MonthlyPeriod;

    public function getPublishedAndClosed(): Collection;

    public function close(int $id, int $closedByUserId): void;

    public function reopen(int $id): void;
}
