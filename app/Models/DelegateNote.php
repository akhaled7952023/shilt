<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegateNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'content',
        'created_by',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }
}
