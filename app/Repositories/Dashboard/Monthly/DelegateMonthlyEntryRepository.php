<?php

namespace App\Repositories\Dashboard\Monthly;

use App\Models\DelegateMonthlyEntry;

class DelegateMonthlyEntryRepository implements IDelegateMonthlyEntryRepository
{
    public function getAll(array $filters = [])
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getById($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function create(array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function update($model, array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function delete($model)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getForPeriodWithRelations(int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }
}
