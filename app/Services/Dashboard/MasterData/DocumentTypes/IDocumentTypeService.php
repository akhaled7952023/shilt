<?php
namespace App\Services\Dashboard\MasterData\DocumentTypes;

interface IDocumentTypeService
{
    public function getAll();
    public function getAllActive();
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function toggleActive($id);
    public function getForDelegates();
    public function getForVehicles();
}
