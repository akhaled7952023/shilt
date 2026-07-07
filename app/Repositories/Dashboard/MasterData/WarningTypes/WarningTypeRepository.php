<?php

namespace App\Repositories\Dashboard\MasterData\WarningTypes;

use App\Models\WarningEntry;
use App\Models\WarningType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WarningTypeRepository implements IWarningTypeRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = WarningType::query();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name->ar', 'like', "%{$term}%")
                  ->orWhere('name->en', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function getAllActive(): Collection
    {
        return WarningType::where('is_active', true)->orderBy('id')->get();
    }

    public function getById(int $id): ?WarningType
    {
        return WarningType::find($id);
    }

    public function isReferenced(int $id): bool
    {
        return WarningEntry::where('warning_type_id', $id)->exists();
    }

    public function create(array $data): WarningType
    {
        return WarningType::create($data);
    }

    public function update(WarningType $warningType, array $data): bool
    {
        return $warningType->update($data);
    }

    public function delete(WarningType $warningType): bool
    {
        return (bool) $warningType->delete();
    }

    public function toggleActive(WarningType $warningType): bool
    {
        return $warningType->update(['is_active' => !$warningType->is_active]);
    }
}
