<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegateDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegate_id',
        'document_type_id',
        'document_number',
        'file_path',
        'issue_date',
        'expiry_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function scopeExpiringBefore($query, $date)
    {
        return $query->where('expiry_date', '<=', $date);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}
