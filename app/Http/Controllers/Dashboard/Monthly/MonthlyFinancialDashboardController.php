<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Models\ChefzDelegateSettlement;
use App\Models\ChefzImportBatch;
use App\Models\CompanyExpense;
use App\Models\HungerStationFtrDelegateDeduction;
use App\Models\HungerStationFtrSettlement;
use App\Models\MonthlyPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonthlyFinancialDashboardController extends Controller
{
    public function show(MonthlyPeriod $period, Request $request): View
    {
        $period->load('platform', 'closedBy');
        $data = $this->buildData($period, $request);
        return view('dashboard.monthly.periods.financial_dashboard', $data);
    }

    public function pdf(MonthlyPeriod $period, Request $request): View
    {
        $period->load('platform', 'closedBy');
        $data = $this->buildData($period, $request);
        return view('dashboard.monthly.periods.financial_dashboard_pdf', $data);
    }

    private function buildData(MonthlyPeriod $period, Request $request): array
    {
        $platformCode = $period->platform?->code ?? '';

        try {
            return match($platformCode) {
                'hungerstation' => $this->buildHsData($period),
                'the-chefz'    => $this->buildChefzData($period, $request),
                default        => abort(404, 'لا توجد لوحة مالية لهذه المنصة.'),
            };
        } catch (\DivisionByZeroError $e) {
            abort(500, 'خطأ في حساب البيانات: قسمة على صفر.');
        }
    }

    private static function safeDivide(float $num, float $den, float $fallback = 0.0): float
    {
        return $den != 0 ? round($num / $den, 2) : $fallback;
    }

    // ── HungerStation FTR ────────────────────────────────────────────────────────

    private function buildHsData(MonthlyPeriod $period): array
    {
        $platformCode = 'hungerstation';

        $settlements = HungerStationFtrSettlement::where('monthly_period_id', $period->id)
            ->with('delegate')
            ->orderByDesc('net_salary')
            ->get();

        $expenses = CompanyExpense::where('monthly_period_id', $period->id)
            ->orderByDesc('amount')
            ->get();

        $totalOrders     = (int)   $settlements->sum('total_orders');
        $basicPayment    = (float) $settlements->sum('basic_payment');
        $distancePayment = (float) $settlements->sum('distance_payment');
        $penaltiesTotal  = (float) $settlements->sum('total_platform_penalties');
        $stackingTotal   = (float) $settlements->sum('stacking_deduction');
        $riderBalance    = (float) $settlements->sum('rider_balance');
        $netSalaries     = (float) $settlements->sum('net_salary');
        $totalDrivers    = $settlements->count();
        $companyExpenses = (float) $expenses->sum('amount');

        $netProfit = round($basicPayment - $companyExpenses, 2);
        $avgSalary = self::safeDivide($netSalaries, (float) $totalDrivers);
        $avgOrders = self::safeDivide((float) $totalOrders, (float) $totalDrivers);

        $grossFees  = $basicPayment;
        $netRevenue = $basicPayment;

        $kpis = compact(
            'totalOrders', 'basicPayment', 'distancePayment',
            'penaltiesTotal', 'stackingTotal', 'riderBalance',
            'netSalaries', 'totalDrivers', 'companyExpenses',
            'netProfit', 'avgSalary', 'avgOrders',
            'grossFees', 'netRevenue'
        );

        $topBySalary     = $settlements->sortByDesc('net_salary')->take(10)->values();
        $topByOrders     = $settlements->sortByDesc('total_orders')->take(10)->values();
        $topByDeductions = $settlements->sortByDesc('total_platform_penalties')->take(10)->values();
        $topByComp       = collect();

        $typeLabels = HungerStationFtrDelegateDeduction::TYPE_LABELS;
        $deductionsByType = DB::table('hungerstation_ftr_delegate_deductions')
            ->where('monthly_period_id', $period->id)
            ->selectRaw('deduction_type, SUM(amount) as total')
            ->groupBy('deduction_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($d) => [
                'label' => $typeLabels[$d->deduction_type] ?? $d->deduction_type,
                'value' => (float) $d->total,
            ]);

        $dailyData          = collect();
        $deductionNotes     = collect();
        $compensationNotes  = collect();

        $expensesByCategory = $expenses->groupBy('category')->map(fn($g) => [
            'label' => $g->first()->category,
            'value' => round($g->sum('amount'), 2),
        ])->values();

        $maxSalary = max(1.0, (float) ($topBySalary->max('net_salary') ?? 1));
        $maxOrders = max(1, (int) ($topByOrders->max('total_orders') ?? 1));

        // Chefz-specific placeholders
        $payoutFilter    = 0;
        $payout1Batch    = null;
        $payout2Batch    = null;
        $isMonthComplete = false;

        return compact(
            'period', 'platformCode', 'kpis', 'settlements', 'expenses',
            'dailyData',
            'topBySalary', 'topByOrders', 'topByDeductions', 'topByComp',
            'deductionsByType', 'deductionNotes', 'compensationNotes',
            'expensesByCategory',
            'maxSalary', 'maxOrders',
            'payoutFilter', 'payout1Batch', 'payout2Batch', 'isMonthComplete'
        );
    }

    // ── Chefz ────────────────────────────────────────────────────────────────────

    private function buildChefzData(MonthlyPeriod $period, Request $request): array
    {
        $platformCode = 'the-chefz';
        // 0 = Monthly Total (P1+P2 combined), 1 = First Payout, 2 = Second Payout
        $payoutFilter = (int) $request->query('payout', 0);

        $payout1Batch = ChefzImportBatch::where('monthly_period_id', $period->id)
            ->where('payout_number', 1)->where('status', 'completed')->first();
        $payout2Batch = ChefzImportBatch::where('monthly_period_id', $period->id)
            ->where('payout_number', 2)->where('status', 'completed')->first();
        $isMonthComplete = $payout1Batch && $payout2Batch;

        // Base settlement query
        $baseQuery = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
            ->with('delegate');

        if ($payoutFilter === 1 || $payoutFilter === 2) {
            // Single payout: filter directly
            $settlements = $baseQuery->where('payout_number', $payoutFilter)
                ->orderByDesc('net_salary')
                ->get();
        } else {
            // Monthly total: sum P1+P2 per delegate
            $settlements = $this->buildMonthlyTotalSettlements($period);
        }

        // KPI aggregates from the resolved collection
        $totalOrders           = (int)   $settlements->sum('total_orders');
        $grossFees             = (float) $settlements->sum('gross_delivery_fees');
        $vatTotal              = (float) $settlements->sum('chefz_tax_amount');
        $commissionTotal       = (float) $settlements->sum('company_share_amount');
        $platformDeductions    = (float) $settlements->sum('platform_deductions');
        $platformCompensations = (float) $settlements->sum('platform_compensations');
        $bonusTotal            = (float) $settlements->sum('bonus_total');
        $positiveBonus         = (float) $settlements->sum('positive_bonus');
        $netSalaries           = (float) $settlements->sum('net_salary');
        $totalDrivers          = $settlements->count();

        // Company net revenue = company_share_amount (what the company retains from Chefz)
        $netRevenue = round($commissionTotal, 2);
        // Net profit = company_share - overhead (Chefz has no company expenses tracked)
        $netProfit  = round($commissionTotal, 2);

        $avgSalary = self::safeDivide($netSalaries, (float) $totalDrivers);
        $avgOrders = self::safeDivide((float) $totalOrders, (float) $totalDrivers);

        $kpis = compact(
            'totalOrders', 'grossFees', 'vatTotal', 'commissionTotal',
            'platformDeductions', 'platformCompensations',
            'bonusTotal', 'positiveBonus',
            'netSalaries', 'totalDrivers', 'netRevenue', 'netProfit',
            'avgSalary', 'avgOrders'
        );

        // Daily trend — scoped to payout if filtered
        $dailyData = $this->buildChefzDailyData($period, $payoutFilter);

        $topBySalary = $settlements->sortByDesc('net_salary')->take(10)->values();
        $topByOrders = $settlements->sortByDesc('total_orders')->take(10)->values();

        $topByDeductions   = collect();
        $topByComp         = collect();
        $deductionsByType  = collect();
        $deductionNotes    = collect();
        $compensationNotes = collect();
        $expensesByCategory = collect();
        $expenses          = collect();

        $maxSalary = max(1.0, (float) ($topBySalary->max('net_salary') ?? 1));
        $maxOrders = max(1, (int) ($topByOrders->max('total_orders') ?? 1));

        return compact(
            'period', 'platformCode', 'kpis', 'settlements', 'expenses',
            'dailyData',
            'topBySalary', 'topByOrders', 'topByDeductions', 'topByComp',
            'deductionsByType', 'deductionNotes', 'compensationNotes',
            'expensesByCategory',
            'maxSalary', 'maxOrders',
            'payoutFilter', 'payout1Batch', 'payout2Batch', 'isMonthComplete'
        );
    }

    /**
     * Aggregate P1 + P2 per delegate into virtual "monthly total" settlement rows.
     */
    private function buildMonthlyTotalSettlements(MonthlyPeriod $period): \Illuminate\Support\Collection
    {
        $all = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
            ->with('delegate')
            ->get()
            ->groupBy('delegate_id');

        return $all->map(function ($payouts) {
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
            }
            return $row;
        })->values()->sortByDesc('net_salary')->values();
    }

    /**
     * Build daily order/revenue trend for Chefz, optionally scoped to a payout.
     */
    private function buildChefzDailyData(MonthlyPeriod $period, int $payoutFilter): \Illuminate\Support\Collection
    {
        return DB::table('chefz_orders')
            ->where('monthly_period_id', $period->id)
            ->when($payoutFilter > 0, fn($q) => $q->where('payout_number', $payoutFilter))
            ->selectRaw("
                DATE(order_date) as date,
                COUNT(*) as orders,
                COALESCE(SUM(delivery_fee), 0)       as revenue,
                COALESCE(SUM(deduction_amount), 0)   as deductions,
                COALESCE(SUM(compensation), 0)       as compensations,
                COALESCE(SUM(GREATEST(bonus_amount,0)),0) as bonus
            ")
            ->groupByRaw('DATE(order_date)')
            ->orderBy('date')
            ->get();
    }
}
