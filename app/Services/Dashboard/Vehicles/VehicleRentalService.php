<?php

namespace App\Services\Dashboard\Vehicles;

use App\Models\VehicleRental;
use App\Repositories\Dashboard\VehicleAssignments\IVehicleAssignmentRepository;
use App\Services\AuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleRentalService implements IVehicleRentalService
{
    public function __construct(
        private IVehicleAssignmentRepository $vehicleAssignmentRepository,
        private AuditService                 $auditService,
    ) {}

    private function assertNotAssigned(int $vehicleId): void
    {
        if ($this->vehicleAssignmentRepository->getActive($vehicleId) !== null) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن تعديل الإيجار: المركبة لديها تعيين نشط. أرجع المركبة أولاً.',
            ]);
        }
    }

    public function getForVehicle(int $vehicleId): LengthAwarePaginator
    {
        return VehicleRental::where('vehicle_id', $vehicleId)
            ->with(['monthlyPeriod', 'delegate'])
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function getForPeriod(int $periodId): Collection
    {
        return VehicleRental::where('monthly_period_id', $periodId)
            ->with(['vehicle', 'delegate'])
            ->get();
    }

    public function create(int $vehicleId, array $data): VehicleRental
    {
        $this->assertNotAssigned($vehicleId);

        // BR-VR-05: delegate_id required when payment_by = delegate
        if (isset($data['payment_by']) && $data['payment_by'] === 'delegate' && empty($data['delegate_id'])) {
            throw ValidationException::withMessages([
                'delegate_id' => 'يجب تحديد المندوب عند اختيار دفع المندوب',
            ]);
        }

        return DB::transaction(function () use ($vehicleId, $data) {
            $rental = VehicleRental::create(array_merge($data, ['vehicle_id' => $vehicleId]));
            $this->auditService->log('created', $rental, [], $rental->getAttributes());
            return $rental;
        });
    }

    public function update(int $rentalId, array $data): VehicleRental
    {
        $rental = VehicleRental::findOrFail($rentalId);
        $this->assertNotAssigned($rental->vehicle_id);

        // BR-VR-05
        if (isset($data['payment_by']) && $data['payment_by'] === 'delegate' && empty($data['delegate_id'])) {
            throw ValidationException::withMessages([
                'delegate_id' => 'يجب تحديد المندوب عند اختيار دفع المندوب',
            ]);
        }

        return DB::transaction(function () use ($rental, $data) {
            $old = $rental->getAttributes();
            $rental->update($data);
            $this->auditService->log('updated', $rental, $old, $rental->fresh()->getAttributes());
            return $rental->fresh();
        });
    }

    public function delete(int $rentalId): void
    {
        $rental = VehicleRental::findOrFail($rentalId);
        $this->assertNotAssigned($rental->vehicle_id);

        DB::transaction(function () use ($rental) {
            $this->auditService->log('deleted', $rental, $rental->getAttributes(), []);
            $rental->delete();
        });
    }
}
