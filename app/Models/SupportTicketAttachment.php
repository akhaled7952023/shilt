<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 3 — Attachment on a support ticket or reply.
 * Files are stored on the private storage disk.
 * Max 10 MB per file; allowed MIME types: JPEG, PNG, WEBP, PDF.
 *
 * @property int         $id
 * @property int         $ticket_id
 * @property int|null    $reply_id       NULL = opening message attachment
 * @property string      $uploader_type  'delegate' | 'admin'
 * @property int         $uploader_id
 * @property string      $original_filename
 * @property string      $stored_path    Relative path inside private storage disk
 * @property int         $file_size      Size in bytes
 * @property string      $mime_type
 */
class SupportTicketAttachment extends Model
{
    protected $table = 'support_ticket_attachments';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'reply_id',
        'uploader_type',
        'uploader_id',
        'original_filename',
        'stored_path',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'created_at' => 'datetime',
    ];

    /** Allowed MIME types for upload validation. */
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    /** Maximum file size per attachment in bytes (10 MB). */
    public const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;

    /** Maximum total attachment size per ticket in bytes (30 MB). */
    public const MAX_TOTAL_SIZE_BYTES = 30 * 1024 * 1024;

    // ── Relationships ─────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(SupportTicketReply::class, 'reply_id');
    }
}
