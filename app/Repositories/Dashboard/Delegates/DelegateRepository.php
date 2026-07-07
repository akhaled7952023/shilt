<?php

namespace App\Repositories\Dashboard\Delegates;

use App\Enums\DelegateStatus;
use App\Models\Delegate;
use App\Models\VehicleAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DelegateRepository implements IDelegateRepository
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Delegate::with(['city', 'platform'])->orderBy('name');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('delegate_code', 'like', "%{$term}%")
                  ->orWhere('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $perPage = (int) (\App\Models\SystemSetting::get('items_per_page') ?? 20);

        return $query->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return Delegate::where('status', DelegateStatus::Active)
            ->orderBy('name')
            ->select(['id', 'name'])
            ->get();
    }

    public function getById(int $id): ?Delegate
    {
        return Delegate::find($id);
    }

    public function getWithRelations(int $id): ?Delegate
    {
        return Delegate::with([
            'city',
            'platform',
            'vehicleAssignments' => fn($q) => $q->with('vehicle.vehicleType')->orderByDesc('assigned_at'),
            'delegateDocuments.documentType',
        ])->find($id);
    }

    public function findByNationalId(string $nationalId): ?Delegate
    {
        return Delegate::where('national_id', $nationalId)->first();
    }

    public function hasActiveVehicleAssignment(int $delegateId): bool
    {
        return VehicleAssignment::where('delegate_id', $delegateId)
            ->where('is_active', true)
            ->exists();
    }

    public function create(array $data): Delegate
    {
        return Delegate::create($data);
    }

    public function update(Delegate $delegate, array $data): bool
    {
        return $delegate->update($data);
    }

    public function delete(Delegate $delegate): bool
    {
        return $delegate->delete();
    }
}
