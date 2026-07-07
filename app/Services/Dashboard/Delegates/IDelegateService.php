<?php

namespace App\Services\Dashboard\Delegates;

use App\Enums\DelegateStatus;
use App\Models\Delegate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface IDelegateService
{
    public function getAll(array $filters = []): LengthAwarePaginator;

    public function getActive(): Collection;

    public function getById(int $id): Delegate;

    public function getWithRelations(int $id): Delegate;

    public function create(
        array $data,
        ?UploadedFile $photo = null,
        ?UploadedFile $iqamaImage = null,
        ?UploadedFile $licenseImage = null
    ): Delegate;

    public function update(
        int $id,
        array $data,
        ?UploadedFile $photo = null,
        ?UploadedFile $iqamaImage = null,
        ?UploadedFile $licenseImage = null
    ): Delegate;

    public function updateStatus(int $id, DelegateStatus $status): Delegate;

    public function updatePassword(int $id, string $password): Delegate;

    public function delete(int $id): void;
}
