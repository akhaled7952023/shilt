<?php
namespace App\Services;

use App\Utils\ImageManger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function __construct(private ImageManger $imageManger) {}

    public function uploadDocument(UploadedFile $file, string $entityType, int $entityId): string
    {
        $directory = "{$entityType}/{$entityId}/documents";
        $filename  = $this->imageManger->uploadSingleImage($directory, $file, 'public');
        return "{$directory}/{$filename}";
    }

    public function uploadProfilePhoto(UploadedFile $file, string $entityType, int $entityId): string
    {
        $directory = "{$entityType}/{$entityId}/photos";
        $filename  = $this->imageManger->uploadSingleImage($directory, $file, 'public');
        return "{$directory}/{$filename}";
    }

    public function delete(string $relativePath): void
    {
        Storage::disk('public')->delete($relativePath);
    }

    public function getPublicUrl(string $relativePath): string
    {
        return Storage::disk('public')->url($relativePath);
    }
}
