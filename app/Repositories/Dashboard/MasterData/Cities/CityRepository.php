<?php

namespace App\Repositories\Dashboard\MasterData\Cities;

use App\Models\City;
use App\Models\Delegate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CityRepository implements ICityRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = City::query();

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.ar')) LIKE ?", ["%{$term}%"])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.en')) LIKE ?", ["%{$term}%"]);
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function getAllActive(): Collection
    {
        return City::where('is_active', true)->orderBy('id')->get();
    }

    public function getById(int $id): ?City
    {
        return City::find($id);
    }

    public function isReferenced(int $id): bool
    {
        return Delegate::where('city_id', $id)->exists();
    }

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(City $city, array $data): bool
    {
        return $city->update($data);
    }

    public function delete(City $city): bool
    {
        return (bool) $city->delete();
    }

    public function toggleActive(City $city): bool
    {
        return $city->update(['is_active' => !$city->is_active]);
    }
}
