<?php
namespace App\Services\Dashboard\Monthly;

class DelegateMonthlyEntryService implements IDelegateMonthlyEntryService
{
    public function getForPeriod(int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getForDelegate(int $delegateId, int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function createOrUpdate(array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function delete($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function recalculate(int $entryId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getBulkForPeriod(int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }
}
