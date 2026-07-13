<?php

namespace App\Http\Controllers\Delegate\Support;

use App\Enums\FinancialRequestStatus;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Delegate;
use App\Models\FinancialRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketReply;
use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Models\User;
use App\Services\Support\AdminActivityLogger;
use App\Services\Support\NotificationService;
use App\Services\Support\SlaCalculator;
use App\Services\Support\TicketAttachmentService;
use App\Services\Support\TicketAuditLogger;
use App\Services\Support\TicketNumberGenerator;
use App\Services\Support\TicketReplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalSupportTicketController extends Controller
{
    public function __construct(
        private readonly TicketNumberGenerator   $numberGenerator,
        private readonly SlaCalculator           $slaCalculator,
        private readonly TicketReplyService      $replyService,
        private readonly TicketAttachmentService $attachmentService,
        private readonly TicketAuditLogger       $auditLogger,
        private readonly AdminActivityLogger     $activityLogger,
        private readonly NotificationService     $notificationService,
    ) {}

    // ── P6-003: My Tickets list ───────────────────────────────────────────────

    public function index(Request $request): View
    {
        /** @var Delegate $delegate */
        $delegate = auth('delegate')->user();
        $status   = $request->query('status');

        // Query level isolation: always scope to authenticated delegate
        $query = SupportTicket::forDelegate($delegate->id)
            ->orderByDesc('last_activity_at');

        if ($status && TicketStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        $tickets      = $query->paginate(15)->withQueryString();
        $statusCounts = SupportTicket::forDelegate($delegate->id)
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('portal.support.tickets.index', compact('tickets', 'status', 'statusCounts'));
    }

    // ── P6-001 / P6-002: Two-step ticket creation ────────────────────────────

    public function create(Request $request): View
    {
        $categoryValue = $request->query('category');

        $category = $categoryValue ? TicketCategory::tryFrom($categoryValue) : null;
        if ($category && $category->isPortalSubmittable()) {
            return view('portal.support.tickets.create-step2', compact('category'));
        }

        return view('portal.support.tickets.create-step1');
    }

    // ── P6-002: Store new ticket ─────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        /** @var Delegate $delegate */
        $delegate      = auth('delegate')->user();
        $categoryValue = $request->input('category');
        $category      = TicketCategory::tryFrom((string) $categoryValue);

        if (! $category || ! $category->isPortalSubmittable()) {
            return back()->withInput()->with('error', __('portal.ticket_invalid_category'));
        }

        $rules = [
            'category'      => ['required', 'string'],
            'subject'       => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpeg,jpg,png,webp,pdf'],
        ];

        if ($category->isFinancial()) {
            $rules['requested_amount'] = ['required', 'numeric', 'min:0.01', 'max:999999.99'];
        }

        $validated = $request->validate($rules);
        $files     = $request->file('attachments', []);
        $now       = now();

        $ticket = DB::transaction(function () use ($delegate, $category, $validated, $files, $now) {
            // generate() uses lockForUpdate; must be inside a transaction
            $ticketNumber = $this->numberGenerator->generate();

            $ticket                    = new SupportTicket();
            $ticket->ticket_number     = $ticketNumber;
            $ticket->platform          = 'hungerstation';
            $ticket->source            = TicketSource::Portal;
            $ticket->delegate_id       = $delegate->id;
            $ticket->category          = $category;
            $ticket->priority          = TicketPriority::Normal;
            $ticket->subject           = $validated['subject'];
            $ticket->status            = TicketStatus::Open;
            $ticket->opened_at         = $now;
            $ticket->last_activity_at  = $now;
            $ticket->created_by        = $delegate->id;
            $ticket->save();

            // Apply SLA deadlines for the normal priority (per spec: always normal on creation)
            $this->slaCalculator->applyToTicket($ticket);
            $ticket->save();

            // Auto-create FinancialRequest for financial categories (P6-002 AC)
            if ($category->isFinancial()) {
                FinancialRequest::create([
                    'ticket_id'        => $ticket->id,
                    'delegate_id'      => $delegate->id,
                    'request_category' => $category,
                    'requested_amount' => $validated['requested_amount'],
                    'requested_notes'  => $validated['description'] ?? null,
                    'status'           => FinancialRequestStatus::Pending,
                ]);
            }

            // Store initial description as first reply from delegate only when
            // description was provided — content column is NOT NULL TEXT.
            // Status stays Open — do NOT use TicketReplyService::postDelegateReply()
            // here because that service sets status=AwaitingDelegate which is wrong
            // for a brand-new ticket that no admin has seen yet.
            if (! empty($validated['description'])) {
                $firstReply                     = new SupportTicketReply();
                $firstReply->ticket_id          = $ticket->id;
                $firstReply->author_type        = 'delegate';
                $firstReply->author_delegate_id = $delegate->id;
                $firstReply->content            = $validated['description'];
                $firstReply->is_internal_note   = false;
                $firstReply->save();

                foreach ($files as $file) {
                    $this->attachmentService->storeForReply(
                        $ticket, $firstReply, $file, 'delegate', $delegate->id
                    );
                }
            }

            $this->auditLogger->logDelegate(
                ticket: $ticket,
                delegateId: $delegate->id,
                delegateLabel: $delegate->name,
                action: 'ticket_created',
                description: "Delegate opened ticket {$ticket->ticket_number}.",
            );

            try {
                $this->activityLogger->logDelegate(
                    delegateId: $delegate->id,
                    delegateLabel: $delegate->name,
                    action: 'ticket_created',
                    description: "Delegate {$delegate->name} opened {$ticket->ticket_number}.",
                    subject: $ticket,
                    subjectLabel: $ticket->ticket_number,
                );
            } catch (\Throwable) {}

            return $ticket;
        });

        // P4-004: notify all support admins about the new ticket
        try {
            $this->notificationService->sendToSupportAdmins(
                category:       NotificationCategory::TicketNew,
                title:          "تذكرة جديدة من {$delegate->name}: {$ticket->ticket_number}",
                body:           $ticket->subject,
                actionUrl:      route('dashboard.support.tickets.show', $ticket),
                notifiableType: SupportTicket::class,
                notifiableId:   $ticket->id,
            );
        } catch (\Throwable) {}

        return redirect()
            ->route('portal.support.tickets.show', $ticket)
            ->with('success', __('portal.ticket_created_success', ['number' => $ticket->ticket_number]));
    }

    // ── P6-004: Ticket detail ────────────────────────────────────────────────

    public function show(SupportTicket $ticket): View
    {
        /** @var Delegate $delegate */
        $delegate = auth('delegate')->user();

        // Ownership enforced at BOTH query level (explicit check) AND policy level
        // — delegates must never access another delegate's ticket
        if ($ticket->delegate_id !== $delegate->id) {
            abort(403);
        }

        // Reload the ticket via a scoped query to guarantee isolation even if
        // the route model binding resolved a stale/injected instance
        $ticket = SupportTicket::forDelegate($delegate->id)->findOrFail($ticket->id);

        $ticket->load([
            'replies' => fn ($q) => $q
                ->where('is_internal_note', false)  // internal notes never shown to delegate
                ->orderBy('created_at'),
            'replies.attachments',
            'attachments',
            'financialRequest',
        ]);

        // Mark support notifications for this ticket as read
        try {
            DB::table('notifications')
                ->where('recipient_type', 'delegate')
                ->where('recipient_id', $delegate->id)
                ->where('notifiable_type', SupportTicket::class)
                ->where('notifiable_id', $ticket->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable) {}

        return view('portal.support.tickets.show', compact('ticket', 'delegate'));
    }

    // ── P6-005: Reply submission ──────────────────────────────────────────────

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        /** @var Delegate $delegate */
        $delegate = auth('delegate')->user();

        // Ownership check at BOTH levels
        if ($ticket->delegate_id !== $delegate->id) {
            abort(403);
        }

        // Scope re-fetch ensures the check cannot be bypassed by stale binding
        $ticket = SupportTicket::forDelegate($delegate->id)->findOrFail($ticket->id);

        if (! $ticket->canDelegateReply()) {
            return back()->with('error', __('portal.ticket_reply_not_allowed'));
        }

        $validated = $request->validate([
            'content'       => ['required', 'string', 'min:1', 'max:10000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpeg,jpg,png,webp,pdf'],
        ]);

        $files = $request->file('attachments', []);

        // Track grace period state before the service changes the status
        $wasInGracePeriod = $ticket->isWithinGracePeriod();

        $this->replyService->postDelegateReply(
            ticket: $ticket,
            content: $validated['content'],
            delegate: $delegate,
            files: $files,
        );

        // Per spec: "Delegate replies while resolved → status changes to reopened"
        // postDelegateReply sets AwaitingDelegate; override to Reopened for grace replies
        if ($wasInGracePeriod) {
            $ticket->refresh();
            $ticket->status = TicketStatus::Reopened;
            $ticket->save();

            $this->auditLogger->logDelegate(
                ticket: $ticket,
                delegateId: $delegate->id,
                delegateLabel: $delegate->name,
                action: 'ticket_reopened',
                description: 'Delegate replied during grace period; ticket reopened.',
            );

            // P4-006: notify all support admins that the ticket was reopened
            try {
                $this->notificationService->sendToSupportAdmins(
                    category:       NotificationCategory::TicketReopened,
                    title:          "تذكرة أُعيد فتحها: {$ticket->ticket_number}",
                    body:           "أرسل المندوب {$delegate->name} رداً ضمن فترة السماح؛ تمت إعادة فتح التذكرة.",
                    actionUrl:      route('dashboard.support.tickets.show', $ticket),
                    notifiableType: SupportTicket::class,
                    notifiableId:   $ticket->id,
                );
            } catch (\Throwable) {}
        }

        return back()->with('success', __('portal.reply_sent_success'));
    }

    // ── Attachment download (ownership-verified) ──────────────────────────────

    public function downloadAttachment(SupportTicket $ticket, SupportTicketAttachment $attachment): StreamedResponse
    {
        /** @var Delegate $delegate */
        $delegate = auth('delegate')->user();

        // Ticket must belong to this delegate
        if ($ticket->delegate_id !== $delegate->id) {
            abort(403);
        }

        // Attachment must belong to this ticket (prevents cross-ticket download)
        if ($attachment->ticket_id !== $ticket->id) {
            abort(403);
        }

        return $this->attachmentService->download($attachment);
    }
}
