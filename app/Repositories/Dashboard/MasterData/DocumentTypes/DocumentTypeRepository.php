<?php

namespace App\Repositories\Dashboard\MasterData\DocumentTypes;

use App\Enums\DocumentAppliesTo;
use App\Models\DelegateDocument;
use App\Models\DocumentType;
use App\Models\VehicleDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DocumentTypeRepository implements IDocumentTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = DocumentType::query();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name->ar', 'like', "%{$term}%")
                  ->orWhere('name->en', 'like', "%{$term}%");
            });
        }

        if (isset($filters['applies_to'])) {
            $query->where('applies_to', $filters['applies_to']);
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function getAllActive(): Collection
    {
        return DocumentType::where('is_active', true)->orderBy('id')->get();
    }

    public function getForDelegates(): Collection
    {
        return DocumentType::where('applies_to', DocumentAppliesTo::Delegate)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function getForVehicles(): Collection
    {
        return DocumentType::where('applies_to', DocumentAppliesTo::Vehicle)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function getById(int $id): ?DocumentType
    {
        return DocumentType::find($id);
    }

    public function isReferenced(int $id): bool
    {
        return DelegateDocument::where('document_type_id', $id)->exists()
            || VehicleDocument::where('document_type_id', $id)->exists();
    }

    public function create(array $data): DocumentType
    {
        return DocumentType::create($data);
    }

    public function update(DocumentType $documentType, array $data): bool
    {
        return $documentType->update($data);
    }

    public function delete(DocumentType $documentType): bool
    {
        return (bool) $documentType->delete();
    }

    public function toggleActive(DocumentType $documentType): bool
    {
        return $documentType->update(['is_active' => !$documentType->is_active]);
    }
}
