<?php

namespace App\Services\Support;

use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketReply;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Phase 3 — Handles attachment upload and secure download for support tickets.
 *
 * Files are stored on the 'local' (private) disk at:
 *   tickets/{year}/{ticket_id}/{uuid}.{extension}
 *
 * No direct public URLs are ever generated. Downloads are served through a
 * controller that validates ownership before streaming.
 */
class TicketAttachmentService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    /**
     * Store an uploaded file and create a SupportTicketAttachment row.
     *
     * @param  SupportTicket      $ticket
     * @param  SupportTicketReply $reply
     * @param  UploadedFile       $file
     * @param  string             $uploaderType  'admin' or 'delegate'
     * @param  int                $uploaderId
     */
    public function storeForReply(
        SupportTicket $ticket,
        SupportTicketReply $reply,
        UploadedFile $file,
        string $uploaderType,
        int $uploaderId,
    ): SupportTicketAttachment {
        $year      = now()->format('Y');
        $uuid      = Str::uuid()->toString();
        $extension = strtolower($file->getClientOriginalExtension());
        $directory = "tickets/{$year}/{$ticket->id}";
        $filename  = "{$uuid}.{$extension}";

        Storage::disk('local')->putFileAs($directory, $file, $filename);

        $attachment = new SupportTicketAttachment();
        $attachment->ticket_id         = $ticket->id;
        $attachment->reply_id          = $reply->id;
        $attachment->uploader_type     = $uploaderType;
        $attachment->uploader_id       = $uploaderId;
        $attachment->original_filename = $file->getClientOriginalName();
        $attachment->stored_path       = "{$directory}/{$filename}";
        $attachment->file_size         = $file->getSize();
        $attachment->mime_type         = $file->getMimeType();
        $attachment->created_at        = now();
        $attachment->save();

        return $attachment;
    }

    /**
     * Stream a private attachment to the browser.
     * Caller must have already verified ownership/authorization.
     */
    public function download(SupportTicketAttachment $attachment): StreamedResponse
    {
        abort_unless(
            Storage::disk('local')->exists($attachment->stored_path),
            404,
            'Attachment file not found.',
        );

        return Storage::disk('local')->download(
            $attachment->stored_path,
            $attachment->original_filename,
            ['Content-Type' => $attachment->mime_type],
        );
    }

    /** Validate MIME type against the allowed list. */
    public function isAllowedMimeType(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true);
    }

    public function getAllowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }
}
