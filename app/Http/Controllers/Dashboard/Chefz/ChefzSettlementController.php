<?php

namespace App\Http\Controllers\Dashboard\Chefz;

use App\Http\Controllers\Controller;
use App\Models\ChefzDelegateSettlement;
use App\Models\ChefzImportBatch;
use App\Models\MonthlyPeriod;
use App\Services\Dashboard\Chefz\ChefzSettlementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChefzSettlementController extends Controller
{
    public function __construct(
        private readonly ChefzSettlementService $service,
    ) {}

    // ── Settlement List ──────────────────────────────────────────────────────────

    public function index(MonthlyPeriod $period, Request $request): View
    {
        $this->abortIfWrongPlatform($period);

        // Payout tabs: 1 = First, 2 = Second, 0 = Monthly Total
        $payoutFilter = (int) $request->query('payout', 0);

        $payout1Batch  = ChefzImportBatch::where('monthly_period_id', $period->id)->where('payout_number', 1)->where('status', 'completed')->first();
        $payout2Batch  = ChefzImportBatch::where('monthly_period_id', $period->id)->where('payout_number', 2)->where('status', 'completed')->first();
        $isMonthComplete = $payout1Batch && $payout2Batch;

        if ($payoutFilter === 0) {
            // Monthly total: aggregate P1 + P2 per delegate
            $settlements = $this->buildMonthlyTotalList($period);
        } else {
            $settlements = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
                ->where('payout_number', $payoutFilter)
                ->with(['delegate'])
                ->orderByDesc('net_salary')
                ->get();

            $settlements->each(function ($s) {
                $s->computed_status = $this->service->settlementStatus($s);
            });
        }

        $totals = [
            'total_orders'         => $settlements->sum('total_orders'),
            'gross_delivery_fees'  => $settlements->sum('gross_delivery_fees'),
            'chefz_tax_amount'     => $settlements->sum('chefz_tax_amount'),
            'company_share_amount' => $settlements->sum('company_share_amount'),
            'net_salary'           => $settlements->sum('net_salary'),
        ];

        return view('dashboard.monthly.chefz.settlement_index', compact(
            'period', 'settlements', 'totals', 'payoutFilter',
            'payout1Batch', 'payout2Batch', 'isMonthComplete'
        ));
    }

    // ── Delegate Workspace ───────────────────────────────────────────────────────

    public function show(MonthlyPeriod $period, ChefzDelegateSettlement $settlement, Request $request): View
    {
        $this->abortIfWrongPlatform($period);
        abort_if($settlement->monthly_period_id !== $period->id, 404);

        $settlement->load(['delegate', 'deductions', 'updatedBy', 'createdBy']);

        $computedStatus  = $this->service->settlementStatus($settlement);
        $activeTab       = $request->query('tab', 'overview');
        $payout1Batch    = ChefzImportBatch::where('monthly_period_id', $period->id)->where('payout_number', 1)->where('status', 'completed')->first();
        $payout2Batch    = ChefzImportBatch::where('monthly_period_id', $period->id)->where('payout_number', 2)->where('status', 'completed')->first();
        $isMonthComplete = $payout1Batch && $payout2Batch;

        // Load the other payout settlement for navigation
        $otherPayoutNumber = $settlement->payout_number == 1 ? 2 : 1;
        $otherPayout = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
            ->where('delegate_id', $settlement->delegate_id)
            ->where('payout_number', $otherPayoutNumber)
            ->first();

        return view('dashboard.monthly.chefz.settlement_show', compact(
            'period', 'settlement', 'computedStatus', 'activeTab',
            'payout1Batch', 'payout2Batch', 'isMonthComplete', 'otherPayout'
        ));
    }

    // ── Guard ────────────────────────────────────────────────────────────────────

    private function abortIfWrongPlatform(MonthlyPeriod $period): void
    {
        if ($period->platform?->code !== 'the-chefz') {
            abort(404, 'هذه الفترة لا تنتمي لمنصة شيفز.');
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────────

    /**
     * Build a virtual "monthly total" collection by summing P1 + P2 per delegate.
     */
    private function buildMonthlyTotalList(MonthlyPeriod $period): \Illuminate\Support\Collection
    {
        $all = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
            ->with('delegate')
            ->get()
            ->groupBy('delegate_id');

        return $all->map(function ($payouts) {
            $first = $payouts->first();
            $virtualRow = clone $first;
            if ($payouts->count() > 1) {
                $virtualRow->total_orders           = $payouts->sum('total_orders');
                $virtualRow->gross_delivery_fees    = $payouts->sum('gross_delivery_fees');
                $virtualRow->chefz_tax_amount       = $payouts->sum('chefz_tax_amount');
                $virtualRow->company_share_amount   = $payouts->sum('company_share_amount');
                $virtualRow->platform_compensations = $payouts->sum('platform_compensations');
                $virtualRow->platform_deductions    = $payouts->sum('platform_deductions');
                $virtualRow->bonus_total            = $payouts->sum('bonus_total');
                $virtualRow->positive_bonus         = $payouts->sum('positive_bonus');
                $virtualRow->commission_total       = $payouts->sum('commission_total');
                $virtualRow->deductions_total       = $payouts->sum('deductions_total');
                $virtualRow->net_salary             = $payouts->sum('net_salary');
            }
            $virtualRow->computed_status = 'calculated';
            return $virtualRow;
        })->values();
    }
}
