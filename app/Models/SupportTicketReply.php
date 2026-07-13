<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Phase 3 — Support Ticket Reply model.
 *
 * @property int         $id
 * @property int         $ticket_id
 * @property string      $author_type     'delegate' | 'admin'
 * @property int|null    $author_delegate_id
 * @property int|null    $author_user_id
 * @property string      $content
 * @property bool        $is_internal_note  True = admin-only; never shown to delegate
 */
class SupportTicketReply extends Model
{
    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'ticket_id',
        'author_type',
        'author_delegate_id',
        'author_user_id',
        'content',
        'is_internal_note',
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function authorDelegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class, 'author_delegate_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'reply_id');
    }
}
