<?php

namespace App\Services\Dashboard\Delegates;

use App\Enums\DelegateStatus;
use App\Models\Delegate;
use App\Repositories\Dashboard\Delegates\IDelegateRepository;
use App\Services\AuditService;
use App\Services\FileUploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DelegateService implements IDelegateService
{
    public function __construct(
        private IDelegateRepository $delegateRepository,
        private FileUploadService   $fileUploadService,
        private AuditService        $auditService,
    ) {}

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return $this->delegateRepository->getAll($filters);
    }

    public function getActive(): Collection
    {
        return $this->delegateRepository->getActive();
    }

    public function getActiveByPlatformCode(string $platformCode): Collection
    {
        return $this->delegateRepository->getActiveByPlatformCode($platformCode);
    }

    public function getById(int $id): Delegate
    {
        $delegate = $this->delegateRepository->getById($id);
        if ($delegate === null) {
            throw new ModelNotFoundException('Delegate not found');
        }
        return $delegate;
    }

    public function getWithRelations(int $id): Delegate
    {
        $delegate = $this->delegateRepository->getWithRelations($id);
        if ($delegate === null) {
            throw new ModelNotFoundException('Delegate not found');
        }
        return $delegate;
    }

    public function create(
        array $data,
        ?UploadedFile $photo = null,
        ?UploadedFile $iqamaImage = null,
        ?UploadedFile $licenseImage = null
    ): Delegate {
        return DB::transaction(function () use ($data, $photo, $iqamaImage, $licenseImage) {
            unset($data['profile_photo'], $data['iqama_image'], $data['driving_license_image']);

            $delegate = $this->delegateRepository->create($data);

            $imageUpdates = [];
            if ($photo) {
                $imageUpdates['profile_photo'] = $this->fileUploadService->uploadProfilePhoto($photo, 'delegates', $delegate->id);
            }
            if ($iqamaImage) {
                $imageUpdates['iqama_image'] = $this->fileUploadService->uploadDocument($iqamaImage, 'delegates', $delegate->id);
            }
            if ($licenseImage) {
                $imageUpdates['driving_license_image'] = $this->fileUploadService->uploadDocument($licenseImage, 'delegates', $delegate->id);
            }
            if ($imageUpdates) {
                $this->delegateRepository->update($delegate, $imageUpdates);
                $delegate->refresh();
            }

            $this->auditService->log('created', $delegate, [], $delegate->getAttributes());
            return $delegate;
        });
    }

    public function update(
        int $id,
        array $data,
        ?UploadedFile $photo = null,
        ?UploadedFile $iqamaImage = null,
        ?UploadedFile $licenseImage = null
    ): Delegate {
        $delegate   = $this->getById($id);
        $oldValues  = $delegate->getAttributes();
        $oldPhoto   = $delegate->profile_photo;
        $oldIqama   = $delegate->iqama_image;
        $oldLicense = $delegate->driving_license_image;

        unset($data['profile_photo'], $data['iqama_image'], $data['driving_license_image']);

        return DB::transaction(function () use ($delegate, $data, $photo, $iqamaImage, $licenseImage, $oldValues, $oldPhoto, $oldIqama, $oldLicense) {
            $newPhoto   = null;
            $newIqama   = null;
            $newLicense = null;

            if ($photo) {
                $newPhoto = $this->fileUploadService->uploadProfilePhoto($photo, 'delegates', $delegate->id);
                $data['profile_photo'] = $newPhoto;
            }
            if ($iqamaImage) {
                $newIqama = $this->fileUploadService->uploadDocument($iqamaImage, 'delegates', $delegate->id);
                $data['iqama_image'] = $newIqama;
            }
            if ($licenseImage) {
                $newLicense = $this->fileUploadService->uploadDocument($licenseImage, 'delegates', $delegate->id);
                $data['driving_license_image'] = $newLicense;
            }

            $this->delegateRepository->update($delegate, $data);
            $delegate->refresh();

            $this->auditService->log('updated', $delegate, $oldValues, $delegate->getAttributes());

            if ($newPhoto   && $oldPhoto)   $this->fileUploadService->delete($oldPhoto);
            if ($newIqama   && $oldIqama)   $this->fileUploadService->delete($oldIqama);
            if ($newLicense && $oldLicense) $this->fileUploadService->delete($oldLicense);

            return $delegate;
        });
    }

    public function updateStatus(int $id, DelegateStatus $status): Delegate
    {
        $delegate  = $this->getById($id);
        $oldStatus = $delegate->status;

        return DB::transaction(function () use ($delegate, $status, $oldStatus) {
            $this->delegateRepository->update($delegate, ['status' => $status]);
            $delegate->refresh();

            $this->auditService->log(
                'status_changed',
                $delegate,
                ['status' => $oldStatus->value],
                ['status' => $status->value]
            );

            return $delegate;
        });
    }

    public function updatePassword(int $id, string $password): Delegate
    {
        $delegate = $this->getById($id);

        return DB::transaction(function () use ($delegate, $password) {
            $this->delegateRepository->update($delegate, ['password' => $password]);
            $delegate->refresh();
            $this->auditService->log('password_changed', $delegate, [], ['password' => '***']);
            return $delegate;
        });
    }

    public function delete(int $id): void
    {
        $delegate = $this->getById($id);

        if ($this->delegateRepository->hasActiveVehicleAssignment($id)) {
            throw ValidationException::withMessages([
                'error' => 'لا يمكن الحذف: المندوب لديه مركبة معينة نشطة',
            ]);
        }

        DB::transaction(function () use ($delegate) {
            $this->auditService->log('deleted', $delegate, $delegate->getAttributes(), []);
            $this->delegateRepository->delete($delegate);
        });
    }
}
