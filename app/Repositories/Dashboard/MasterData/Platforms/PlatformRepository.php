<?php

namespace App\Repositories\Dashboard\MasterData\Platforms;

use App\Models\DelegateMonthlyEntry;
use App\Models\DelegatePlatformAssignment;
use App\Models\Platform;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PlatformRepository implements IPlatformRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Platform::query();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function getAllActive(): Collection
    {
        return Platform::where('is_active', true)->orderBy('name')->get();
    }

    public function getById(int $id): ?Platform
    {
        return Platform::find($id);
    }

    public function getByCode(string $code): ?Platform
    {
        return Platform::where('code', $code)->first();
    }

    public function isReferenced(int $id): bool
    {
        return DelegatePlatformAssignment::where('platform_id', $id)->exists()
            || DelegateMonthlyEntry::where('platform_id', $id)->exists();
    }

    public function create(array $data): Platform
    {
        return Platform::create($data);
    }

    public function update(Platform $platform, array $data): bool
    {
        return $platform->update($data);
    }

    public function delete(Platform $platform): bool
    {
        return (bool) $platform->delete();
    }

    public function toggleActive(Platform $platform): bool
    {
        return $platform->update(['is_active' => !$platform->is_active]);
    }
}
