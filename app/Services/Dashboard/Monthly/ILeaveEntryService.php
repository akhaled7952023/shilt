<?php
namespace App\Services\Dashboard\Monthly;

interface ILeaveEntryService
{
    public function getForDelegate(int $delegateId, int $periodId);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getTotalForEntry(int $delegateId, int $periodId);
    public function getForPeriod(int $periodId);
}
