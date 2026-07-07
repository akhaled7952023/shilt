<?php
namespace App\Services\Dashboard\MasterData\Cities;

interface ICityService
{
    public function getAll();
    public function getAllActive();
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function toggleActive($id);
}
