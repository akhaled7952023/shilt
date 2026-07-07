<?php

namespace App\Models;

use App\Enums\DocumentAppliesTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class DocumentType extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'applies_to', 'is_required', 'is_active'];

    public array $translatable = ['name'];

    protected $casts = [
        'applies_to'  => DocumentAppliesTo::class,
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function delegateDocuments()
    {
        return $this->hasMany(DelegateDocument::class);
    }

    public function vehicleDocuments()
    {
        return $this->hasMany(VehicleDocument::class);
    }
}
