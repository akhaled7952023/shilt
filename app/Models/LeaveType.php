<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LeaveType extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'is_active'];

    public array $translatable = ['name'];
}
