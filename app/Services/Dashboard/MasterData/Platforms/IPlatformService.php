<?php
namespace App\Services\Dashboard\MasterData\Platforms;

interface IPlatformService
{
    public function getAll(array $filters = []);
    public function getAllActive();
    public function getById($id);
    public function getActive();
    public function getByCode(string $code);
    public function getWithSettings(int $id);
}
