<?php

namespace App\Repositories\Dashboard\MasterData\DocumentTypes;

use App\Models\DocumentType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IDocumentTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getForDelegates(): Collection;
    public function getForVehicles(): Collection;
    public function getById(int $id): ?DocumentType;
    public function isReferenced(int $id): bool;
    public function create(array $data): DocumentType;
    public function update(DocumentType $documentType, array $data): bool;
    public function delete(DocumentType $documentType): bool;
    public function toggleActive(DocumentType $documentType): bool;
}
