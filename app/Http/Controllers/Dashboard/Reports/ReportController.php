<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Http\Controllers\Controller;
use App\Models\ChefzDelegateSettlement;
use App\Models\HungerStationFtrSettlement;
use App\Models\MonthlyPeriod;
use App\Models\Platform;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ── Monthly Payment Report (period-based, not date-based) ────────────────

    public function monthlyReport(Request $request)
    {
        $currentYear  = (int) date('Y');
        $selectedYear = (int) ($request->input('year', $currentYear));

        // Resolve available years from periods table
        $availableYears = MonthlyPeriod::selectRaw('YEAR(start_date) as yr')
            ->groupByRaw('YEAR(start_date)')
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$currentYear];
        }

        // Default selected months = current month only
        $allMonths = range(1, 12);
        $defaultMonth = (int) date('n');
        $selectedMonths = array_filter(
            array_map('intval', $request->input('months', [$defaultMonth])),
            fn($m) => $m >= 1 && $m <= 12
        );
        if (empty($selectedMonths)) {
            $selectedMonths = [$defaultMonth];
        }

        // Resolve MonthlyPeriod IDs matching selected year + months
        $periods = MonthlyPeriod::where('year', $selectedYear)
            ->whereIn('month', $selectedMonths)
            ->with('platform')
            ->orderBy('month')
            ->get();

        $periodIds = $periods->pluck('id')->toArray();

        // HS settlements for selected periods
        $hsSettlements = collect();
        if (!empty($periodIds)) {
            $hsSettlements = HungerStationFtrSettlement::whereIn('monthly_period_id', $periodIds)
                ->with(['delegate', 'period'])
                ->orderByDesc('net_salary')
                ->get();
        }

        // Chefz payout filter: 0 = monthly total (group P1+P2), 1 = First Payout, 2 = Second Payout
        $chefzPayoutFilter = (int) $request->input('chefz_payout', 0);

        // Chefz settlements for selected periods
        $chefzSettlements = collect();
        if (!empty($periodIds) && class_exists(ChefzDelegateSettlement::class)) {
            $raw = ChefzDelegateSettlement::whereIn('monthly_period_id', $periodIds)
                ->with(['delegate', 'period'])
                ->orderBy('monthly_period_id')
                ->orderBy('delegate_id')
                ->orderBy('payout_number')
                ->get();

            if ($chefzPayoutFilter === 1 || $chefzPayoutFilter === 2) {
                // Show only the selected payout rows
                $chefzSettlements = $raw->where('payout_number', $chefzPayoutFilter)
                    ->sortByDesc('net_salary')
                    ->values();
            } else {
                // Monthly total: group by period+delegate, sum both payouts
                $chefzSettlements = $raw
                    ->groupBy(fn($s) => $s->monthly_period_id . '_' . $s->delegate_id)
                    ->map(function ($payouts) {
                        $first = $payouts->first();
                        $row   = clone $first;
                        if ($payouts->count() > 1) {
                            $row->total_orders           = $payouts->sum('total_orders');
                            $row->gross_delivery_fees    = $payouts->sum('gross_delivery_fees');
                            $row->chefz_tax_amount       = $payouts->sum('chefz_tax_amount');
                            $row->company_share_amount   = $payouts->sum('company_share_amount');
                            $row->platform_compensations = $payouts->sum('platform_compensations');
                            $row->platform_deductions    = $payouts->sum('platform_deductions');
                            $row->bonus_total            = $payouts->sum('bonus_total');
                            $row->positive_bonus         = $payouts->sum('positive_bonus');
                            $row->commission_total       = $payouts->sum('commission_total');
                            $row->deductions_total       = $payouts->sum('deductions_total');
                            $row->net_salary             = $payouts->sum('net_salary');
                            $row->payout_number          = 0; // virtual total
                        }
                        return $row;
                    })
                    ->sortByDesc('net_salary')
                    ->values();
            }
        }

        $arabicMonths = $this->arabicMonths();

        return view('dashboard.reports.monthly_report', compact(
            'availableYears', 'selectedYear', 'allMonths', 'selectedMonths',
            'periods', 'periodIds', 'hsSettlements', 'chefzSettlements',
            'arabicMonths', 'chefzPayoutFilter'
        ));
    }

    // ── Legacy stubs ─────────────────────────────────────────────────────────

    public function monthlyPayment($periodId)
    {
        $period = MonthlyPeriod::findOrFail($periodId);
        return redirect()->route('dashboard.reports.monthly', [
            'year'   => $period->year ?? date('Y'),
            'months' => [$period->month ?? date('n')],
        ]);
    }

    public function delegateHistory($delegateId)
    {
        return view('dashboard.reports.delegate-history');
    }

    public function platformSummary($periodId)
    {
        return view('dashboard.reports.platform-summary');
    }

    public function documentExpiry()
    {
        return view('dashboard.reports.document-expiry');
    }

    public function exportExcel($type)
    {
        return redirect()->back();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function arabicMonths(): array
    {
        return [
            1  => 'يناير',  2  => 'فبراير', 3  => 'مارس',
            4  => 'أبريل',  5  => 'مايو',   6  => 'يونيو',
            7  => 'يوليو',  8  => 'أغسطس',  9  => 'سبتمبر',
            10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
    }
}
