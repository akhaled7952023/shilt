<?php

namespace App\Services\Support;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\TicketStatus;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketReplyService
{
    public function __construct(
        private readonly TicketAuditLogger       $auditLogger,
        private readonly AdminActivityLogger      $activityLogger,
        private readonly SlaCalculator            $slaCalculator,
        private readonly TicketAttachmentService  $attachmentService,
        private readonly EmailNotificationService $emailService,
    ) {}

    /**
     * Post an admin reply or internal note on a ticket.
     *
     * @param  SupportTicket    $ticket
     * @param  string           $content
     * @param  bool             $isInternalNote
     * @param  User             $admin
     * @param  UploadedFile[]   $files
     * @return SupportTicketReply
     */
    public function postAdminReply(
        SupportTicket $ticket,
        string $content,
        bool $isInternalNote,
        User $admin,
        array $files = [],
    ): SupportTicketReply {
        $reply = DB::transaction(function () use ($ticket, $content, $isInternalNote, $admin, $files) {
            $reply = new SupportTicketReply();
            $reply->ticket_id         = $ticket->id;
            $reply->author_type       = 'admin';
            $reply->author_user_id    = $admin->id;
            $reply->content           = $content;
            $reply->is_internal_note  = $isInternalNote;
            $reply->save();

            foreach ($files as $file) {
                $this->attachmentService->storeForReply($ticket, $reply, $file, 'admin', $admin->id);
            }

            $now = now();

            // Record first admin reply timestamp and SLA outcome
            if (! $isInternalNote && ! $ticket->first_reply_at) {
                $ticket->first_reply_at = $now;
                $this->slaCalculator->evaluateFirstResponse($ticket);
            }

            // Update ticket status based on reply type
            if (! $isInternalNote) {
                if ($ticket->status === TicketStatus::AwaitingDelegate
                    || $ticket->status === TicketStatus::Reopened) {
                    $ticket->status = TicketStatus::InProgress;
                }
            }

            $ticket->last_activity_at = $now;
            $ticket->updated_by       = $admin->id;
            $ticket->save();

            $action = $isInternalNote ? 'internal_note_added' : 'replied';
            $this->auditLogger->logAdmin(
                ticket: $ticket,
                admin: $admin,
                action: $action,
                description: $isInternalNote ? 'Internal note added.' : 'Admin posted a reply.',
            );

            if (! $isInternalNote) {
                try {
                    $this->activityLogger->logAdmin(
                        admin: $admin,
                        action: 'admin_replied',
                        description: "Admin replied to ticket {$ticket->ticket_number}.",
                        subject: $ticket,
                        subjectLabel: $ticket->ticket_number,
                    );
                } catch (\Throwable) {}

                // Notify delegate about admin reply
                try {
                    if ($ticket->delegate_id) {
                        DB::table('notifications')->insert([
                            'recipient_type'  => 'delegate',
                            'recipient_id'    => $ticket->delegate_id,
                            'channel'         => NotificationChannel::Portal->value,
                            'category'        => NotificationCategory::TicketReply->value,
                            'title'           => "رد جديد من الدعم على تذكرتك {$ticket->ticket_number}",
                            'body'            => Str::limit($content, 150),
                            'action_url'      => route('portal.support.tickets.show', $ticket),
                            'notifiable_type' => SupportTicketReply::class,
                            'notifiable_id'   => $reply->id,
                            'sent_at'         => now()->toDateTimeString(),
                            'created_at'      => now()->toDateTimeString(),
                        ]);
                    }
                } catch (\Throwable) {}
            }

            return $reply;
        });

        if (! $isInternalNote && $ticket->delegate_id) {
            try {
                $this->emailService->sendToDelegateIfEnabled(
                    $ticket->delegate_id,
                    NotificationCategory::TicketReply,
                    "رد جديد من الدعم على تذكرتك {$ticket->ticket_number}",
                    Str::limit($content, 150),
                    route('portal.support.tickets.show', $ticket),
                );
            } catch (\Throwable) {}
        }

        return $reply;
    }

    /**
     * Post a delegate reply on a ticket.
     *
     * @param  SupportTicket    $ticket
     * @param  string           $content
     * @param  \App\Models\Delegate $delegate
     * @param  UploadedFile[]   $files
     * @return SupportTicketReply
     */
    public function postDelegateReply(
        SupportTicket $ticket,
        string $content,
        \App\Models\Delegate $delegate,
        array $files = [],
    ): SupportTicketReply {
        $reply = DB::transaction(function () use ($ticket, $content, $delegate, $files) {
            $reply = new SupportTicketReply();
            $reply->ticket_id            = $ticket->id;
            $reply->author_type          = 'delegate';
            $reply->author_delegate_id   = $delegate->id;
            $reply->content              = $content;
            $reply->is_internal_note     = false;
            $reply->save();

            foreach ($files as $file) {
                $this->attachmentService->storeForReply($ticket, $reply, $file, 'delegate', $delegate->id);
            }

            $ticket->status           = TicketStatus::AwaitingDelegate;
            $ticket->last_activity_at = now();
            $ticket->save();

            $this->auditLogger->logDelegate(
                ticket: $ticket,
                delegateId: $delegate->id,
                delegateLabel: $delegate->name,
                action: 'replied',
                description: 'Delegate posted a reply.',
            );

            try {
                $this->activityLogger->logDelegate(
                    delegateId: $delegate->id,
                    delegateLabel: $delegate->name,
                    action: 'delegate_replied',
                    description: "Delegate {$delegate->name} replied to ticket {$ticket->ticket_number}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}

            // Notify all admins with support access when delegate replies
            try {
                $adminIds = User::whereNotNull('role_id')
                    ->whereHas('role', fn ($q) => $q->where('permissions', 'like', '%"support"%'))
                    ->pluck('id');

                if ($adminIds->isNotEmpty()) {
                    $now  = now()->toDateTimeString();
                    $rows = $adminIds->map(fn ($id) => [
                        'recipient_type'  => 'admin',
                        'recipient_id'    => $id,
                        'channel'         => NotificationChannel::Portal->value,
                        'category'        => NotificationCategory::TicketReply->value,
                        'title'           => "رد جديد من {$delegate->name} على تذكرة {$ticket->ticket_number}",
                        'body'            => Str::limit($content, 150),
                        'action_url'      => route('dashboard.support.tickets.show', $ticket),
                        'notifiable_type' => SupportTicketReply::class,
                        'notifiable_id'   => $reply->id,
                        'sent_at'         => $now,
                        'created_at'      => $now,
                    ])->toArray();
                    DB::table('notifications')->insert($rows);
                }
            } catch (\Throwable) {}

            return $reply;
        });

        try {
            $this->emailService->sendToAdminSubscribers(
                NotificationCategory::TicketReply,
                "رد جديد من {$delegate->name} على تذكرة {$ticket->ticket_number}",
                Str::limit($content, 150),
                route('dashboard.support.tickets.show', $ticket),
            );
        } catch (\Throwable) {}

        return $reply;
    }
}
