<?php

namespace App\Services\Dashboard\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use App\Repositories\Dashboard\Vehicles\IVehicleRepository;
use App\Services\AuditService;
use App\Services\FileUploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleService implements IVehicleService
{
    public function __construct(
        private IVehicleRepository $vehicleRepository,
        private FileUploadService  $fileUploadService,
        private AuditService       $auditService,
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->vehicleRepository->getAll($filters);
    }

    public function getById(int $id): Vehicle
    {
        $vehicle = $this->vehicleRepository->getById($id);
        if ($vehicle === null) {
            throw new ModelNotFoundException('المركبة غير موجودة');
        }
        return $vehicle;
    }

    public function getAvailable(): Collection
    {
        return $this->vehicleRepository->getAvailable();
    }

    public function getWithRelations(int $id): Vehicle
    {
        $vehicle = $this->vehicleRepository->getWithRelations($id);
        if ($vehicle === null) {
            throw new ModelNotFoundException('المركبة غير موجودة');
        }
        return $vehicle;
    }

    public function create(
        array $data,
        ?UploadedFile $vehicleImage = null,
        ?UploadedFile $registrationImage = null,
        ?UploadedFile $insuranceImage = null
    ): Vehicle {
        if (empty($data['status'])) {
            $data['status'] = VehicleStatus::Available->value;
        }

        unset($data['vehicle_image'], $data['registration_image'], $data['insurance_image']);

        return DB::transaction(function () use ($data, $vehicleImage, $registrationImage, $insuranceImage) {
            $vehicle = $this->vehicleRepository->create($data);

            $imageUpdates = [];
            if ($vehicleImage) {
                $imageUpdates['vehicle_image'] = $this->fileUploadService->uploadProfilePhoto($vehicleImage, 'vehicles', $vehicle->id);
            }
            if ($registrationImage) {
                $imageUpdates['registration_image'] = $this->fileUploadService->uploadDocument($registrationImage, 'vehicles', $vehicle->id);
            }
            if ($insuranceImage) {
                $imageUpdates['insurance_image'] = $this->fileUploadService->uploadDocument($insuranceImage, 'vehicles', $vehicle->id);
            }
            if ($imageUpdates) {
                $this->vehicleRepository->update($vehicle, $imageUpdates);
                $vehicle->refresh();
            }

            $this->auditService->log('created', $vehicle, [], $vehicle->getAttributes());
            return $vehicle;
        });
    }

    public function update(
        int $id,
        array $data,
        ?UploadedFile $vehicleImage = null,
        ?UploadedFile $registrationImage = null,
        ?UploadedFile $insuranceImage = null
    ): Vehicle {
        $vehicle        = $this->getById($id);
        $hasAssignment  = $this->vehicleRepository->hasActiveAssignment($id);
        $oldValues      = $vehicle->getAttributes();
        $oldVehicleImg  = $vehicle->vehicle_image;
        $oldRegImg      = $vehicle->registration_image;
        $oldInsImg      = $vehicle->insurance_image;

        // Cannot manually change status while vehicle has active assignment
        if ($hasAssignment && array_key_exists('status', $data)) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تغيير حالة المركبة يدوياً أثناء وجود تعيين نشط',
            ]);
        }

        unset($data['vehicle_image'], $data['registration_image'], $data['insurance_image']);

        return DB::transaction(function () use ($vehicle, $data, $vehicleImage, $registrationImage, $insuranceImage, $oldValues, $oldVehicleImg, $oldRegImg, $oldInsImg) {
            $newVehicleImg = null;
            $newRegImg     = null;
            $newInsImg     = null;

            if ($vehicleImage) {
                $newVehicleImg = $this->fileUploadService->uploadProfilePhoto($vehicleImage, 'vehicles', $vehicle->id);
                $data['vehicle_image'] = $newVehicleImg;
            }
            if ($registrationImage) {
                $newRegImg = $this->fileUploadService->uploadDocument($registrationImage, 'vehicles', $vehicle->id);
                $data['registration_image'] = $newRegImg;
            }
            if ($insuranceImage) {
                $newInsImg = $this->fileUploadService->uploadDocument($insuranceImage, 'vehicles', $vehicle->id);
                $data['insurance_image'] = $newInsImg;
            }

            $this->vehicleRepository->update($vehicle, $data);
            $vehicle->refresh();

            $this->auditService->log('updated', $vehicle, $oldValues, $vehicle->getAttributes());

            if ($newVehicleImg && $oldVehicleImg) $this->fileUploadService->delete($oldVehicleImg);
            if ($newRegImg     && $oldRegImg)     $this->fileUploadService->delete($oldRegImg);
            if ($newInsImg     && $oldInsImg)     $this->fileUploadService->delete($oldInsImg);

            return $vehicle;
        });
    }

    public function delete(int $id): void
    {
        $vehicle = $this->getById($id);

        if ($this->vehicleRepository->hasOperationalHistory($id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن حذف هذه المركبة لأن لديها سجلات تشغيلية مرتبطة (تعيينات مندوبين أو صيانة أو مخالفات). يُرجى الاحتفاظ بالمركبة لضمان سلامة السجل التاريخي.',
            ]);
        }

        DB::transaction(function () use ($vehicle) {
            $this->auditService->log('deleted', $vehicle, $vehicle->getAttributes(), []);
            $this->vehicleRepository->delete($vehicle);
        });
    }

    public function updateStatus(int $id, VehicleStatus $status): Vehicle
    {
        $vehicle   = $this->getById($id);
        $oldStatus = $vehicle->status;

        return DB::transaction(function () use ($vehicle, $status, $oldStatus) {
            $this->vehicleRepository->update($vehicle, ['status' => $status->value]);

            $this->auditService->log(
                'status_changed',
                $vehicle->fresh(),
                ['status' => $oldStatus->value],
                ['status' => $status->value],
            );

            return $vehicle->fresh();
        });
    }
}
