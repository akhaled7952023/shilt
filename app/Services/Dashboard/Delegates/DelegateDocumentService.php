<?php

namespace App\Services\Dashboard\Delegates;

use App\Enums\DocumentAppliesTo;
use App\Models\DelegateDocument;
use App\Models\DocumentType;
use App\Services\AuditService;
use App\Services\Dashboard\Settings\ISystemSettingService;
use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DelegateDocumentService implements IDelegateDocumentService
{
    public function __construct(
        private FileUploadService      $fileUploadService,
        private AuditService           $auditService,
        private ISystemSettingService  $settingService,
    ) {}

    public function getForDelegate(int $delegateId): Collection
    {
        return DelegateDocument::where('delegate_id', $delegateId)
            ->with(['documentType'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getExpiryStatus(DelegateDocument $document): string
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

    public function store(int $delegateId, array $data, ?UploadedFile $file = null): DelegateDocument
    {
        $docType = DocumentType::findOrFail($data['document_type_id']);

        if ($docType->applies_to !== DocumentAppliesTo::Delegate) {
            throw ValidationException::withMessages([
                'document_type_id' => 'نوع الوثيقة غير صالح للمندوبين',
            ]);
        }

        $path = null;

        return DB::transaction(function () use ($delegateId, $data, $file, &$path) {
            try {
                if ($file) {
                    $path = $this->fileUploadService->uploadDocument($file, 'delegates', $delegateId);
                }

                $document = DelegateDocument::create([
                    'delegate_id'      => $delegateId,
                    'document_type_id' => $data['document_type_id'],
                    'document_number'  => $data['document_number'] ?? null,
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
        $document = DelegateDocument::findOrFail($documentId);
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

        return DelegateDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', $threshold)
            ->with(['delegate', 'documentType'])
            ->get();
    }

    public function getExpired(): Collection
    {
        return DelegateDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with(['delegate', 'documentType'])
            ->get();
    }
}
