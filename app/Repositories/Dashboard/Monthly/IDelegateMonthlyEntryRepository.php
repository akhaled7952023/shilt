<?php

namespace App\Repositories\Dashboard\Monthly;

interface IDelegateMonthlyEntryRepository
{
    public function getAll(array $filters = []);

    public function getById($id);

    public function create(array $data);

    public function update($model, array $data);

    public function delete($model);

    public function getForPeriodWithRelations(int $periodId);
}
