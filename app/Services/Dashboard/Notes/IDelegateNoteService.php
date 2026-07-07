<?php
namespace App\Services\Dashboard\Notes;

interface IDelegateNoteService
{
    public function getForDelegate(int $delegateId);
    public function create(array $data);
    public function delete($id);
}
