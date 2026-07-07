<?php
namespace App\Services\Dashboard\Monthly;

class ViolationEntryService implements IViolationEntryService
{
    public function getForDelegate(int $delegateId, int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function create(array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function update($id, array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function delete($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getTotalForEntry(int $delegateId, int $periodId)
    {
        throw new \RuntimeException('Not implemented');
    }
}
