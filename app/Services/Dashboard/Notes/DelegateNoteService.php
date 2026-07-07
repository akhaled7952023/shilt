<?php
namespace App\Services\Dashboard\Notes;

class DelegateNoteService implements IDelegateNoteService
{
    public function getForDelegate(int $delegateId)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function create(array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function delete($id)
    {
        throw new \RuntimeException('Not implemented');
    }
}
