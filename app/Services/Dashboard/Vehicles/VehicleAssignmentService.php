<?php

namespace App\Services\Dashboard\Vehicles;

use App\Enums\DelegateStatus;
use App\Enums\VehicleStatus;
use App\Models\VehicleAssignment;
use App\Repositories\Dashboard\Delegates\IDelegateRepository;
use App\Repositories\Dashboard\VehicleAssignments\IVehicleAssignmentRepository;
use App\Repositories\Dashboard\Vehicles\IVehicleRepository;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleAssignmentService implements IVehicleAssignmentService
{
    public function __construct(
        private IVehicleAssignmentRepository $vehicleAssignmentRepository,
        private IVehicleRepository           $vehicleRepository,
        private IDelegateRepository          $delegateRepository,
        private AuditService                 $auditService,
    ) {}

    public function assign(int $vehicleId, array $data): VehicleAssignment
    {
        $vehicle  = $this->vehicleRepository->getById($vehicleId);
        $delegate = $this->delegateRepository->getById($data['delegate_id']);

        if ($delegate === null) {
            throw ValidationException::withMessages(['delegate_id' => 'المندوب غير موجود']);
        }

        // BR-VA-03: delegate must be active
        if ($delegate->status !== DelegateStatus::Active) {
            throw ValidationException::withMessages(['error' => 'لا يمكن تعيين مركبة لمندوب غير نشط']);
        }

        // BR-VA-07: assigned_at cannot be in the future
        if (Carbon::parse($data['assigned_at'])->isFuture()) {
            throw ValidationException::withMessages(['assigned_at' => 'تاريخ التعيين لا يمكن أن يكون في المستقبل']);
        }

        return DB::transaction(function () use ($vehicleId, $vehicle, $delegate, $data) {
            // Auto-close existing active assignment
            $existing = $this->vehicleAssignmentRepository->getActive($vehicleId);
            if ($existing !== null) {
                $this->vehicleAssignmentRepository->update($existing, [
                    'returned_at' => $data['assigned_at'],
                    'is_active'   => false,
                ]);
                $this->auditService->log('returned', $existing, $existing->getAttributes(), $existing->fresh()->getAttributes());
            }

            $assignment = $this->vehicleAssignmentRepository->create([
                'vehicle_id'  => $vehicleId,
                'delegate_id' => $delegate->id,
                'assigned_at' => $data['assigned_at'],
                'is_active'   => true,
                'notes'       => $data['notes'] ?? null,
            ]);

            $this->vehicleRepository->update($vehicle, ['status' => VehicleStatus::Assigned]);

            $this->auditService->log('assigned', $assignment, [], $assignment->getAttributes());

            return $assignment;
        });
    }

    public function unassign(int $vehicleId): void
    {
        $assignment = $this->vehicleAssignmentRepository->getActive($vehicleId);

        if ($assignment === null) {
            throw ValidationException::withMessages(['error' => 'لا يوجد تعيين نشط لهذه المركبة']);
        }

        $vehicle = $this->vehicleRepository->getById($vehicleId);

        DB::transaction(function () use ($assignment, $vehicle) {
            $old = $assignment->getAttributes();

            $this->vehicleAssignmentRepository->update($assignment, [
                'returned_at' => now()->toDateString(),
                'is_active'   => false,
            ]);

            $this->vehicleRepository->update($vehicle, ['status' => VehicleStatus::Available]);

            $this->auditService->log('returned', $assignment, $old, $assignment->fresh()->getAttributes());
        });
    }

    public function returnVehicle(int $assignmentId, array $data): VehicleAssignment
    {
        $assignment = VehicleAssignment::findOrFail($assignmentId);
        $vehicle    = $this->vehicleRepository->getById($assignment->vehicle_id);

        // returned_at >= assigned_at
        if (Carbon::parse($data['returned_at'])->lt(Carbon::parse($assignment->assigned_at))) {
            throw ValidationException::withMessages(['returned_at' => 'تاريخ الإرجاع لا يمكن أن يكون قبل تاريخ التعيين']);
        }

        if (Carbon::parse($data['returned_at'])->isFuture()) {
            throw ValidationException::withMessages(['returned_at' => 'تاريخ الإرجاع لا يمكن أن يكون في المستقبل']);
        }

        return DB::transaction(function () use ($assignment, $vehicle, $data) {
            $old = $assignment->getAttributes();

            $this->vehicleAssignmentRepository->update($assignment, [
                'returned_at' => $data['returned_at'],
                'is_active'   => false,
                'notes'       => $data['notes'] ?? $assignment->notes,
            ]);

            $this->vehicleRepository->update($vehicle, ['status' => VehicleStatus::Available]);

            $this->auditService->log('returned', $assignment, $old, $assignment->fresh()->getAttributes());

            return $assignment->fresh();
        });
    }

    public function getActive(int $vehicleId): ?VehicleAssignment
    {
        return $this->vehicleAssignmentRepository->getActive($vehicleId);
    }

    public function getForVehicle(int $vehicleId): Collection
    {
        return $this->vehicleAssignmentRepository->getForVehicle($vehicleId);
    }

    public function getForDelegate(int $delegateId): Collection
    {
        return $this->vehicleAssignmentRepository->getForDelegate($delegateId);
    }
}
