<?php

namespace App\Services\Dashboard\Vehicles;

use App\Enums\DocumentAppliesTo;
use App\Models\DocumentType;
use App\Models\VehicleDocument;
use App\Services\AuditService;
use App\Services\Dashboard\Settings\ISystemSettingService;
use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleDocumentService implements IVehicleDocumentService
{
    public function __construct(
        private FileUploadService     $fileUploadService,
        private AuditService          $auditService,
        private ISystemSettingService $settingService,
    ) {}

    public function getForVehicle(int $vehicleId): Collection
    {
        return VehicleDocument::where('vehicle_id', $vehicleId)
            ->with(['documentType'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getExpiryStatus(VehicleDocument $document): string
    {
        if (! $document->expiry_date) {
            return 'valid';
        }

        $today = now()->startOfDay();

        if ($document->expiry_date->lt($today)) {
            return 'expired';
        }

        $alertDays = (int) $this->settingService->get('document_expiry_alert_days', 30);

        if ($document->expiry_date->lte($today->copy()->addDays($alertDays))) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function store(int $vehicleId, array $data, UploadedFile $file): VehicleDocument
    {
        $docType = DocumentType::findOrFail($data['document_type_id']);

        if ($docType->applies_to !== DocumentAppliesTo::Vehicle) {
            throw ValidationException::withMessages([
                'document_type_id' => 'نوع الوثيقة غير صالح للمركبات',
            ]);
        }

        $path = null;

        return DB::transaction(function () use ($vehicleId, $data, $file, &$path) {
            try {
                $path = $this->fileUploadService->uploadDocument($file, 'vehicles', $vehicleId);

                $document = VehicleDocument::create([
                    'vehicle_id'       => $vehicleId,
                    'document_type_id' => $data['document_type_id'],
                    'file_path'        => $path,
                    'issue_date'       => $data['issue_date'] ?? null,
                    'expiry_date'      => $data['expiry_date'] ?? null,
                    'notes'            => $data['notes'] ?? null,
                ]);

                $this->auditService->log('created', $document, [], $document->getAttributes());

                return $document;
            } catch (\Exception $e) {
                if ($path) {
                    $this->fileUploadService->delete($path);
                }
                throw $e;
            }
        });
    }

    public function delete(int $documentId): void
    {
        $document = VehicleDocument::findOrFail($documentId);
        $path     = $document->file_path;

        DB::transaction(function () use ($document) {
            $this->auditService->log('deleted', $document, $document->getAttributes(), []);
            $document->delete();
        });

        if ($path) {
            $this->fileUploadService->delete($path);
        }
    }

    public function getExpiring(int $days): Collection
    {
        $threshold = now()->addDays($days);

        return VehicleDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', $threshold)
            ->with(['vehicle', 'documentType'])
            ->get();
    }

    public function getExpired(): Collection
    {
        return VehicleDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with(['vehicle', 'documentType'])
            ->get();
    }
}
