<?php

namespace App\Services\Dashboard\Delegates;

use App\Models\DelegateDocument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface IDelegateDocumentService
{
    public function getForDelegate(int $delegateId): Collection;
    public function getExpiryStatus(DelegateDocument $document): string;
    public function store(int $delegateId, array $data, ?UploadedFile $file = null): DelegateDocument;
    public function delete(int $documentId): void;
    public function getExpiring(int $days): Collection;
    public function getExpired(): Collection;
}
