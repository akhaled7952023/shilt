<?php
namespace App\Services\Dashboard\Monthly;

interface IDelegateMonthlyEntryService
{
    public function getForPeriod(int $periodId);
    public function getForDelegate(int $delegateId, int $periodId);
    public function createOrUpdate(array $data);
    public function delete($id);
    public function recalculate(int $entryId);
    public function getBulkForPeriod(int $periodId);
}
