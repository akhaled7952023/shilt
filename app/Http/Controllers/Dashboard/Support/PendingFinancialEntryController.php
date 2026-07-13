<?php

namespace App\Http\Controllers\Dashboard\Support;

use App\Http\Controllers\Controller;
use App\Models\MonthlyPeriod;
use App\Models\PendingFinancialEntry;
use App\Services\Support\FinancialRequestReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PendingFinancialEntryController extends Controller
{
    public function __construct(
        private readonly FinancialRequestReviewService $reviewService,
    ) {}

    public function index(Request $request): View
    {
        // Batch 5 — list all pending entries with period/status filters
        abort(501);
    }

    public function byPeriod(MonthlyPeriod $period): View
    {
        // Batch 5 — all pending entries for a specific settlement period
        abort(501);
    }

    public function markImported(Request $request, PendingFinancialEntry $entry): RedirectResponse
    {
        // Batch 5 — set status=imported, record imported_by, settlement_id, adjustment_id
        abort(501);
    }

    // ── Cancel (P2-006) ───────────────────────────────────────────────────────

    public function cancel(Request $request, PendingFinancialEntry $entry): RedirectResponse
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403, 'Only super admins can cancel pending financial entries.');

        if (! $entry->canBeCancelled()) {
            flash()->error($entry->isImported()
                ? 'لا يمكن إلغاء قيد مالي تم استيراده بالفعل.'
                : 'هذا القيد المالي مُلغى مسبقاً.');
            return back();
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'cancel_reason.required' => 'يرجى إدخال سبب الإلغاء.',
            'cancel_reason.min'      => 'يجب أن لا يقل السبب عن 5 أحرف.',
        ]);

        try {
            $this->reviewService->cancel($entry, $validated['cancel_reason'], Auth::user());
        } catch (\RuntimeException) {
            flash()->error('لا يمكن إلغاء هذا القيد في حالته الحالية.');
            return back();
        }

        flash()->success('تم إلغاء القيد المالي المعلق وإعادة الطلب إلى حالة الانتظار.');

        // Redirect to the parent ticket if accessible, otherwise back
        $ticket = $entry->financialRequest?->ticket;
        return $ticket
            ? redirect()->route('dashboard.support.tickets.show', $ticket)
            : back();
    }
}
