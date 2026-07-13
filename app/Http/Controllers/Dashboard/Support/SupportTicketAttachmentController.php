<?php

namespace App\Http\Controllers\Dashboard\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Services\Support\TicketAttachmentService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportTicketAttachmentController extends Controller
{
    public function __construct(private readonly TicketAttachmentService $attachmentService) {}

    /**
     * Serve a private attachment file.
     * Delegates may only download attachments from tickets they own.
     * Admins may download any attachment from any ticket.
     */
    public function download(SupportTicket $ticket, SupportTicketAttachment $attachment): StreamedResponse
    {
        // Validate attachment belongs to the ticket
        abort_if($attachment->ticket_id !== $ticket->id, 404);

        // Admin path: any authenticated admin may download
        if (Auth::guard('web')->check()) {
            return $this->attachmentService->download($attachment);
        }

        // Delegate path: only the ticket owner
        if (Auth::guard('delegate')->check()) {
            abort_if($ticket->delegate_id !== Auth::guard('delegate')->id(), 403);
            return $this->attachmentService->download($attachment);
        }

        abort(403);
    }
}
