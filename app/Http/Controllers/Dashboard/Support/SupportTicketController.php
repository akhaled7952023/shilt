<?php

namespace App\Http\Controllers\Dashboard\Support;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\MonthlyPeriod;
use App\Models\SupportNotification;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\Support\TicketLifecycleService;
use App\Services\Support\TicketReplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly TicketLifecycleService $lifecycleService,
        private readonly TicketReplyService     $replyService,
    ) {}

    // ── Queue ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $filters = $request->only([
            'status', 'category', 'priority', 'source',
            'period_id', 'sla_status',
            'date_from', 'date_to', 'search',
        ]);

        $query = SupportTicket::with(['delegate', 'relatedPeriod'])
            ->where('platform', 'hungerstation')
            ->orderBy('last_activity_at', 'desc');

        // Status filter (default: show active tickets)
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->whereNotIn('status', [TicketStatus::Closed->value]);
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (! empty($filters['period_id'])) {
            $query->where('related_monthly_period_id', $filters['period_id']);
        }

        if (! empty($filters['sla_status'])) {
            match ($filters['sla_status']) {
                'overdue_response'   => $query->overdueFirstResponse(),
                'overdue_resolution' => $query->overdueResolution(),
                default              => null,
            };
        }

        if (! empty($filters['date_from'])) {
            $query->where('opened_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('opened_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        // Advanced free-text search (P1-016)
        if (! empty($filters['search']) && strlen($filters['search']) >= 2) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('ticket_number', 'like', $term . '%')
                  ->orWhere('subject', 'like', '%' . $term . '%')
                  ->orWhereHas('delegate', function ($dq) use ($term) {
                      $dq->where('name', 'like', '%' . $term . '%')
                         ->orWhere('platform_id', 'like', '%' . $term . '%')
                         ->orWhere('phone', 'like', '%' . $term . '%');
                  });
            });
        }

        $tickets = $query->paginate(25)->withQueryString();

        // Status counts for tabs
        $statusCounts = SupportTicket::where('platform', 'hungerstation')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $periods    = MonthlyPeriod::orderByDesc('year')->orderByDesc('month')
                          ->limit(24)->get(['id', 'label', 'year', 'month']);
        $categories = TicketCategory::cases();
        $priorities = TicketPriority::cases();
        $sources    = TicketSource::cases();

        return view('dashboard.support.tickets.index', compact(
            'tickets', 'filters', 'statusCounts', 'periods',
            'categories', 'priorities', 'sources',
        ));
    }

    // ── Detail ────────────────────────────────────────────────────────────────

    public function show(SupportTicket $ticket): View
    {
        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        $ticket->load([
            'delegate',
            'relatedPeriod',
            'replies.authorDelegate',
            'replies.authorUser',
            'replies.attachments',
            'attachments',
            'financialRequest.reviewer',
            'financialRequest.pendingEntry',
            'financialRequest.delegate',
            'auditLogs',
        ]);

        // Collect unread reply IDs for this admin, then mark them as read
        $replyIds       = $ticket->replies->pluck('id')->toArray();
        $unreadReplyIds = [];
        if (! empty($replyIds)) {
            $unreadReplyIds = SupportNotification::where('recipient_type', 'admin')
                ->where('recipient_id', $admin->id)
                ->whereNull('read_at')
                ->where('notifiable_type', SupportTicketReply::class)
                ->whereIn('notifiable_id', $replyIds)
                ->pluck('notifiable_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            if (! empty($unreadReplyIds)) {
                SupportNotification::where('recipient_type', 'admin')
                    ->where('recipient_id', $admin->id)
                    ->where('notifiable_type', SupportTicketReply::class)
                    ->whereIn('notifiable_id', $replyIds)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        return view('dashboard.support.tickets.show', compact('ticket', 'unreadReplyIds'));
    }

    // ── Assignment (disabled — collaborative model) ───────────────────────────

    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        flash()->info('تم إلغاء نظام التعيين — جميع المسؤولين يمكنهم الرد على التذاكر.');
        return redirect()->route('dashboard.support.tickets.show', $ticket);
    }

    // ── Reply / Internal Note ─────────────────────────────────────────────────

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_if($ticket->isClosedPermanently(), 403, 'Ticket is permanently closed.');

        $validated = $request->validate([
            'content'          => ['required', 'string', 'max:10000'],
            'is_internal_note' => ['sometimes', 'boolean'],
            'attachments'      => ['sometimes', 'array', 'max:3'],
            'attachments.*'    => ['file', 'max:10240', 'mimes:jpeg,jpg,png,webp,pdf'],
        ]);

        $files = $request->hasFile('attachments')
            ? $request->file('attachments')
            : [];

        $this->replyService->postAdminReply(
            ticket: $ticket,
            content: $validated['content'],
            isInternalNote: (bool) ($validated['is_internal_note'] ?? false),
            admin: Auth::user(),
            files: $files,
        );

        flash()->success('تم إرسال الرد بنجاح');
        return redirect()->route('dashboard.support.tickets.show', $ticket)
            ->withFragment('replies');
    }

    // ── Priority Change ───────────────────────────────────────────────────────

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ]);

        $this->lifecycleService->changePriority(
            $ticket,
            TicketPriority::from($validated['priority']),
            Auth::user(),
        );

        flash()->success('تم تحديث الأولوية وإعادة حساب مواعيد SLA');
        return redirect()->route('dashboard.support.tickets.show', $ticket);
    }

    // ── Resolve (grace period start) ──────────────────────────────────────────

    public function close(Request $request, SupportTicket $ticket): RedirectResponse
    {
        // "close" stub is repurposed as "resolve" in the existing route
        // (Force-close is separate — see reopen() using lifecycle::forceClose below)
        abort_if($ticket->isClosedPermanently(), 403, 'Ticket is already permanently closed.');

        /** @var \App\Models\User $admin */
        $admin   = Auth::user();
        $message = trim($request->input('delegate_message', ''));

        // Issue 4+5: post public reply BEFORE changing status
        DB::transaction(function () use ($ticket, $admin, $message) {
            if ($message !== '') {
                $this->replyService->postAdminReply(
                    ticket: $ticket,
                    content: $message,
                    isInternalNote: false,
                    admin: $admin,
                    files: [],
                );
            }
            $this->lifecycleService->resolve($ticket, $admin);
        });

        flash()->success('تم حل التذكرة وبدأت فترة الانتظار');
        return redirect()->route('dashboard.support.tickets.show', $ticket);
    }

    // ── Force Close ───────────────────────────────────────────────────────────

    public function reopen(Request $request, SupportTicket $ticket): RedirectResponse
    {
        // "reopen" route is used for force-close from admin;
        // the real reopen action will be added in a dedicated route in a later batch.
        // For now: if permanently closed, reopen; if not, force-close (super_admin only).
        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        if ($ticket->isClosedPermanently()) {
            abort_unless($admin->isSuperAdmin(), 403);
            $this->lifecycleService->reopen($ticket, $admin);
            flash()->success('تم إعادة فتح التذكرة');
        } else {
            abort_unless($admin->isSuperAdmin(), 403, 'Only super admins can force-close tickets.');

            // Issue 4+5: post public reply BEFORE changing status
            $message = trim($request->input('delegate_message', ''));
            DB::transaction(function () use ($ticket, $admin, $message) {
                if ($message !== '') {
                    $this->replyService->postAdminReply(
                        ticket: $ticket,
                        content: $message,
                        isInternalNote: false,
                        admin: $admin,
                        files: [],
                    );
                }
                $this->lifecycleService->forceClose($ticket, $admin);
            });

            flash()->success('تم إغلاق التذكرة نهائياً');
        }

        return redirect()->route('dashboard.support.tickets.show', $ticket);
    }

    // ── Settlement Objections Sub-queue (P1-013) ──────────────────────────────

    public function objections(Request $request): View
    {
        // Reuse the index view with category pre-filtered
        $request->merge(['category' => TicketCategory::SettlementObjection->value]);
        return $this->index($request);
    }
}
