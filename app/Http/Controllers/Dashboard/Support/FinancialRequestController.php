<?php

namespace App\Http\Controllers\Dashboard\Support;

use App\Enums\FinancialRequestStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\FinancialRequest;
use App\Models\HungerStationFtrDelegateDeduction;
use App\Models\MonthlyPeriod;
use App\Models\SupportTicket;
use App\Services\Support\FinancialRequestReviewService;
use App\Services\Support\TicketReplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinancialRequestController extends Controller
{
    public function __construct(
        private readonly FinancialRequestReviewService $reviewService,
        private readonly TicketReplyService            $replyService,
    ) {}

    // ── Financial Requests Board (P2-007 / P7-005) ────────────────────────────

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'pending');
        $validTabs = ['pending', 'approved', 'rejected', 'all'];
        if (! in_array($tab, $validTabs)) {
            $tab = 'pending';
        }

        $query = FinancialRequest::with(['ticket', 'delegate', 'reviewer'])
            ->whereHas('ticket', fn ($q) => $q->where('platform', 'hungerstation'))
            ->orderByDesc('created_at');

        match ($tab) {
            'pending'  => $query->whereIn('status', [
                FinancialRequestStatus::Pending->value,
                FinancialRequestStatus::NeedsInfo->value,
            ]),
            'approved' => $query->where('status', FinancialRequestStatus::Approved->value),
            'rejected' => $query->where('status', FinancialRequestStatus::Rejected->value),
            default    => null,  // 'all' — no additional filter
        };

        $financialRequests = $query->paginate(25)->withQueryString();

        $statusCounts = FinancialRequest::whereHas('ticket', fn ($q) => $q->where('platform', 'hungerstation'))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.support.financial-requests.index', compact(
            'financialRequests', 'tab', 'statusCounts',
        ));
    }

    // ── Approve (P2-004 / P7-004) ─────────────────────────────────────────────

    public function approve(Request $request, FinancialRequest $financialRequest): RedirectResponse
    {
        $financialRequest->load('ticket');

        if (! $financialRequest->canBeApproved()) {
            flash()->error('هذا الطلب تمت مراجعته مسبقاً ولا يمكن الموافقة عليه.');
            return redirect()->route('dashboard.support.tickets.show', $financialRequest->ticket);
        }

        $allDeductionTypes = array_keys(HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS);
        $allBenefitTypes   = array_keys(HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS);
        $allValidTypes     = array_merge($allDeductionTypes, $allBenefitTypes);

        $validated = $request->validate([
            'approved_amount'   => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'deduction_type'    => ['required', 'string', Rule::in($allValidTypes)],
            'is_benefit'        => ['required', 'boolean'],
            'settlement_month'  => ['required', 'integer', 'min:1', 'max:12'],
            'approved_label'    => ['nullable', 'string', 'max:200'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'delegate_message'  => ['nullable', 'string', 'max:5000'],
        ], [
            'approved_amount.required'  => 'يرجى إدخال المبلغ المعتمد.',
            'approved_amount.min'       => 'يجب أن يكون المبلغ أكبر من صفر.',
            'deduction_type.required'   => 'يرجى اختيار نوع التسوية.',
            'settlement_month.required' => 'يرجى اختيار شهر التسوية.',
        ]);

        // Year defaults to current year — no UI needed
        $validated['settlement_year'] = now()->year;

        try {
            $this->reviewService->approve($financialRequest, $validated, Auth::user());
        } catch (\RuntimeException $e) {
            flash()->error('هذا الطلب تمت مراجعته مسبقاً.');
            return redirect()->route('dashboard.support.tickets.show', $financialRequest->ticket);
        }

        flash()->success('تمت الموافقة على الطلب المالي وتم إنشاء القيد المالي المعلق بنجاح.');
        return redirect()->route('dashboard.support.tickets.show', $financialRequest->ticket);
    }

    // ── Reject (P2-005 / P7-004) ─────────────────────────────────────────────

    public function reject(Request $request, FinancialRequest $financialRequest): RedirectResponse
    {
        $financialRequest->load('ticket');

        if (! $financialRequest->canBeRejected()) {
            flash()->error('هذا الطلب تمت مراجعته مسبقاً ولا يمكن رفضه.');
            return redirect()->route('dashboard.support.tickets.show', $financialRequest->ticket);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rejection_reason.required' => 'يرجى إدخال سبب الرفض.',
            'rejection_reason.min'      => 'يجب أن لا يقل سبب الرفض عن 10 أحرف.',
        ]);

        try {
            $this->reviewService->reject($financialRequest, $validated['rejection_reason'], Auth::user());
        } catch (\RuntimeException $e) {
            flash()->error('هذا الطلب تمت مراجعته مسبقاً.');
            return redirect()->route('dashboard.support.tickets.show', $financialRequest->ticket);
        }

        flash()->success('تم رفض الطلب المالي وإغلاق التذكرة.');
        return redirect()->route('dashboard.support.tickets.show', $financialRequest->ticket);
    }

    // ── Request More Information (pre-Batch-4 improvement) ───────────────────

    public function requestMoreInfo(Request $request, FinancialRequest $financialRequest): RedirectResponse
    {
        abort_if(
            $financialRequest->status === FinancialRequestStatus::Approved
                || $financialRequest->status === FinancialRequestStatus::Rejected,
            403,
            'Cannot request more information on an already-reviewed financial request.'
        );

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $ticket = $financialRequest->ticket;
        $admin  = Auth::user();

        DB::transaction(function () use ($financialRequest, $ticket, $admin, $validated) {
            $this->replyService->postAdminReply(
                ticket: $ticket,
                content: $validated['message'],
                isInternalNote: false,
                admin: $admin,
                files: [],
            );

            $financialRequest->status      = FinancialRequestStatus::NeedsInfo;
            $financialRequest->reviewed_by = $admin->id;
            $financialRequest->reviewed_at = now();
            $financialRequest->save();

            $ticket->refresh();
            $ticket->status = TicketStatus::AwaitingDelegate;
            $ticket->save();
        });

        flash()->success('تم إرسال طلب المعلومات الإضافية للمندوب وتحويل التذكرة لانتظار ردّه');
        return redirect()->route('dashboard.support.tickets.show', $ticket);
    }
}
