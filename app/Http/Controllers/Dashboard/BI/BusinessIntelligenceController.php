<?php

namespace App\Http\Controllers\Dashboard\BI;

use App\Http\Controllers\Controller;
use App\Models\Delegate;
use App\Models\HungerStationFtrDelegateDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusinessIntelligenceController extends Controller
{
    // ── Entry points ─────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $data = $this->buildAllData();
        return view('dashboard.bi.index', $data);
    }

    public function driver(Delegate $delegate): View
    {
        $data = $this->buildDriverData($delegate);
        return view('dashboard.bi.driver', $data);
    }

    // ── Master data builder ───────────────────────────────────────────────────

    private function buildAllData(): array
    {
        $overview          = $this->buildOverview();
        $platformAnalytics = $this->buildPlatformAnalytics();
        $driverRankings    = $this->buildDriverRankings();
        $costAnalysis      = $this->buildCostAnalysis();
        $benefitsAnalysis  = $this->buildBenefitsAnalysis();
        $monthlyTrend      = $this->buildMonthlyTrend();
        $insights          = $this->buildInsights($overview, $platformAnalytics, $driverRankings);
        $periods           = $this->getPeriods();

        return compact(
            'overview', 'platformAnalytics', 'driverRankings',
            'costAnalysis', 'benefitsAnalysis', 'monthlyTrend',
            'insights', 'periods'
        );
    }

    // ── Section: Business Overview ────────────────────────────────────────────

    private function buildOverview(): array
    {
        // Read directly from finalized settlement records — never recalculate
        $hs = DB::table('hungerstation_ftr_settlements')
            ->selectRaw("
                COALESCE(SUM(basic_payment), 0)            as revenue,
                COALESCE(SUM(net_salary), 0)               as driver_pay,
                COALESCE(SUM(total_orders), 0)             as orders,
                COALESCE(SUM(total_platform_penalties), 0) as penalties,
                COALESCE(SUM(housing_allowance), 0)        as housing,
                COALESCE(SUM(company_benefits_total), 0)   as benefits,
                COALESCE(SUM(company_deductions_total), 0) as deductions,
                COUNT(DISTINCT delegate_id)                as drivers
            ")->first();

        $cz = DB::table('chefz_delegate_settlements')
            ->selectRaw("
                COALESCE(SUM(gross_delivery_fees), 0)    as gross,
                COALESCE(SUM(company_share_amount), 0)   as profit,
                COALESCE(SUM(net_salary), 0)             as driver_pay,
                COALESCE(SUM(total_orders), 0)           as orders,
                COALESCE(SUM(platform_compensations), 0) as compensations,
                COALESCE(SUM(positive_bonus), 0)         as bonuses,
                COALESCE(SUM(platform_deductions), 0)    as deductions,
                COALESCE(SUM(chefz_tax_amount), 0)       as vat,
                COUNT(DISTINCT delegate_id)              as drivers
            ")->first();

        $compExp = (float) DB::table('company_expenses')->sum('amount');

        // HS profit = basic_payment - company_expenses (per approved formula)
        $hsRevenue = (float) $hs->revenue;
        $hsProfit  = round($hsRevenue - $compExp, 2);
        $czProfit  = (float) $cz->profit;

        $totalRevenue  = round($hsRevenue + $czProfit, 2);   // company income
        $totalProfit   = round($hsProfit + $czProfit, 2);
        $totalDriverPay = round((float)$hs->driver_pay + (float)$cz->driver_pay, 2);
        $totalOrders   = (int)$hs->orders + (int)$cz->orders;

        $avgProfitPerOrder = $totalOrders > 0 ? round($totalProfit / $totalOrders, 2) : 0;
        $totalDrivers = (int)$hs->drivers + (int)$cz->drivers;
        $avgDriverSalary = $totalDrivers > 0 ? round($totalDriverPay / $totalDrivers, 2) : 0;

        $hsContrib = $totalProfit > 0 ? round($hsProfit / $totalProfit * 100, 1) : 0;
        $czContrib = $totalProfit > 0 ? round($czProfit / $totalProfit * 100, 1) : 0;

        return compact(
            'hsRevenue', 'hsProfit', 'czProfit',
            'totalRevenue', 'totalProfit', 'totalDriverPay',
            'totalOrders', 'totalDrivers',
            'avgProfitPerOrder', 'avgDriverSalary',
            'hsContrib', 'czContrib', 'compExp',
            'hs', 'cz'
        );
    }

    // ── Section: Platform Analytics ───────────────────────────────────────────

    private function buildPlatformAnalytics(): array
    {
        $hsByPeriod = DB::table('hungerstation_ftr_settlements as s')
            ->join('monthly_periods as mp', 'mp.id', '=', 's.monthly_period_id')
            ->selectRaw("
                mp.label,
                mp.year,
                mp.month,
                COALESCE(SUM(s.basic_payment), 0)            as revenue,
                COALESCE(SUM(s.net_salary), 0)               as driver_pay,
                COALESCE(SUM(s.total_orders), 0)             as orders,
                COALESCE(SUM(s.total_platform_penalties), 0) as penalties,
                COALESCE(SUM(s.housing_allowance), 0)        as housing,
                COALESCE(SUM(s.company_benefits_total), 0)   as benefits,
                COUNT(DISTINCT s.delegate_id)                as drivers
            ")
            ->groupByRaw('mp.id, mp.label, mp.year, mp.month')
            ->orderByRaw('mp.year, mp.month')
            ->get();

        $czByPeriod = DB::table('chefz_delegate_settlements as s')
            ->join('monthly_periods as mp', 'mp.id', '=', 's.monthly_period_id')
            ->selectRaw("
                mp.label,
                mp.year,
                mp.month,
                COALESCE(SUM(s.gross_delivery_fees), 0)    as gross,
                COALESCE(SUM(s.company_share_amount), 0)   as profit,
                COALESCE(SUM(s.net_salary), 0)             as driver_pay,
                COALESCE(SUM(s.total_orders), 0)           as orders,
                COALESCE(SUM(s.platform_compensations), 0) as compensations,
                COALESCE(SUM(s.positive_bonus), 0)         as bonuses,
                COALESCE(SUM(s.chefz_tax_amount), 0)       as vat,
                COUNT(DISTINCT s.delegate_id)              as drivers
            ")
            ->groupByRaw('mp.id, mp.label, mp.year, mp.month')
            ->orderByRaw('mp.year, mp.month')
            ->get();

        // Growth % (period over period)
        $hsGrowth = $this->calcGrowthPct($hsByPeriod, 'revenue');
        $czGrowth = $this->calcGrowthPct($czByPeriod, 'profit');

        // Totals for the comparison bar
        $hsTotals = [
            'revenue'    => $hsByPeriod->sum('revenue'),
            'driver_pay' => $hsByPeriod->sum('driver_pay'),
            'orders'     => $hsByPeriod->sum('orders'),
            'drivers'    => DB::table('hungerstation_ftr_settlements')->distinct('delegate_id')->count('delegate_id'),
            'profit'     => $hsByPeriod->sum('revenue'), // HS profit = revenue (no expenses)
        ];
        $czTotals = [
            'gross'      => $czByPeriod->sum('gross'),
            'profit'     => $czByPeriod->sum('profit'),
            'driver_pay' => $czByPeriod->sum('driver_pay'),
            'orders'     => $czByPeriod->sum('orders'),
            'compensations' => $czByPeriod->sum('compensations'),
            'bonuses'    => $czByPeriod->sum('bonuses'),
            'vat'        => $czByPeriod->sum('vat'),
            'drivers'    => DB::table('chefz_delegate_settlements')->distinct('delegate_id')->count('delegate_id'),
        ];

        return compact('hsByPeriod', 'czByPeriod', 'hsGrowth', 'czGrowth', 'hsTotals', 'czTotals');
    }

    private function calcGrowthPct($collection, string $field): float
    {
        if ($collection->count() < 2) return 0.0;
        $last   = (float) $collection->last()->{$field};
        $prev   = (float) $collection->nth(1)->{$field};   // second-to-last
        if ($prev == 0) return 0.0;
        return round(($last - $prev) / $prev * 100, 1);
    }

    // ── Section: Driver Rankings ──────────────────────────────────────────────

    private function buildDriverRankings(): array
    {
        // HS top by salary
        $hsTopSalary = DB::table('hungerstation_ftr_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.net_salary) as val, SUM(s.total_orders) as orders, 'hungerstation' as platform")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // Chefz top by salary
        $czTopSalary = DB::table('chefz_delegate_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.net_salary) as val, SUM(s.total_orders) as orders, 'the-chefz' as platform")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // HS top by orders
        $hsTopOrders = DB::table('hungerstation_ftr_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.total_orders) as val, SUM(s.net_salary) as salary, 'hungerstation' as platform")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // Chefz top by orders
        $czTopOrders = DB::table('chefz_delegate_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.total_orders) as val, SUM(s.net_salary) as salary, 'the-chefz' as platform")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // HS top by distance payment
        $hsTopDistance = DB::table('hungerstation_ftr_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.distance_payment) as val, SUM(s.total_orders) as orders")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // Chefz top by compensations
        $czTopComp = DB::table('chefz_delegate_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.platform_compensations) as val, SUM(s.total_orders) as orders")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // Chefz top by bonus
        $czTopBonus = DB::table('chefz_delegate_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(s.positive_bonus) as val, SUM(s.total_orders) as orders")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // HS highest deductions (manual)
        $hsTopDeductions = DB::table('hungerstation_ftr_delegate_deductions as dd')
            ->join('delegates as d', 'd.id', '=', 'dd.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(dd.amount) as val")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // Chefz highest deductions
        $czTopDeductions = DB::table('chefz_delegate_deductions as dd')
            ->join('delegates as d', 'd.id', '=', 'dd.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code, SUM(dd.amount) as val")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->orderByDesc('val')->take(10)->get();

        // Profit per order (HS: basic_payment per order)
        $hsTopProfitPerOrder = DB::table('hungerstation_ftr_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.id, d.name, d.delegate_code,
                CASE WHEN SUM(s.total_orders) > 0 THEN ROUND(SUM(s.basic_payment)/SUM(s.total_orders),2) ELSE 0 END as val,
                SUM(s.total_orders) as orders")
            ->groupBy('d.id', 'd.name', 'd.delegate_code')
            ->having('orders', '>', 0)
            ->orderByDesc('val')->take(10)->get();

        return compact(
            'hsTopSalary', 'czTopSalary',
            'hsTopOrders', 'czTopOrders',
            'hsTopDistance', 'czTopComp', 'czTopBonus',
            'hsTopDeductions', 'czTopDeductions',
            'hsTopProfitPerOrder'
        );
    }

    // ── Section: Cost Analysis ────────────────────────────────────────────────

    private function buildCostAnalysis(): array
    {
        $typeLabels = HungerStationFtrDelegateDeduction::TYPE_LABELS;

        // Manual deductions by type (HS)
        $hsDedsByType = DB::table('hungerstation_ftr_delegate_deductions')
            ->selectRaw('deduction_type, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('deduction_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($d) => [
                'type'  => $d->deduction_type,
                'label' => $typeLabels[$d->deduction_type] ?? $d->deduction_type,
                'total' => round((float)$d->total, 2),
                'count' => (int)$d->cnt,
            ]);

        $hsDeductionsTotal = $hsDedsByType->sum('total');

        // Chefz platform deductions (from settlements — never recalculate)
        $czDeds = DB::table('chefz_delegate_settlements')
            ->selectRaw('SUM(platform_deductions) as total, SUM(deductions_total) as grand_total')
            ->first();

        // Company expenses by category
        $compExpByCategory = DB::table('company_expenses')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => [
                'label' => $e->category,
                'total' => round((float)$e->total, 2),
                'count' => (int)$e->cnt,
            ]);

        $compExpTotal = $compExpByCategory->sum('total');

        // Monthly expense trend
        $compExpMonthly = DB::table('company_expenses as ce')
            ->leftJoin('monthly_periods as mp', 'mp.id', '=', 'ce.monthly_period_id')
            ->selectRaw('COALESCE(mp.label,\'—\') as label, SUM(ce.amount) as total')
            ->groupByRaw('mp.id, mp.label')
            ->orderByRaw('mp.year, mp.month')
            ->get();

        return compact(
            'hsDedsByType', 'hsDeductionsTotal',
            'czDeds', 'compExpByCategory', 'compExpTotal', 'compExpMonthly'
        );
    }

    // ── Section: Company Benefits Analytics ──────────────────────────────────

    private function buildBenefitsAnalysis(): array
    {
        // HS benefits from settlement columns
        $hsBenefits = DB::table('hungerstation_ftr_settlements')
            ->selectRaw("
                COALESCE(SUM(housing_allowance), 0)      as housing,
                COALESCE(SUM(company_benefits_total), 0) as benefits_total,
                COALESCE(SUM(rider_balance), 0)          as rider_balance
            ")->first();

        // Chefz bonuses/compensations from settlement columns
        $czBenefits = DB::table('chefz_delegate_settlements')
            ->selectRaw("
                COALESCE(SUM(platform_compensations), 0) as compensations,
                COALESCE(SUM(positive_bonus), 0)         as bonuses,
                COALESCE(SUM(bonus_total), 0)            as bonus_total_raw
            ")->first();

        // HS per-driver housing
        $hsBenefitsByDriver = DB::table('hungerstation_ftr_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.name, SUM(s.housing_allowance) as housing, SUM(s.company_benefits_total) as benefits")
            ->groupBy('d.id', 'd.name')
            ->having(DB::raw('SUM(s.housing_allowance) + SUM(s.company_benefits_total)'), '>', 0)
            ->orderByDesc('benefits')
            ->take(10)->get();

        // Chefz per-driver bonuses
        $czBenefitsByDriver = DB::table('chefz_delegate_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw("d.name, SUM(s.platform_compensations) as comp, SUM(s.positive_bonus) as bonus")
            ->groupBy('d.id', 'd.name')
            ->having(DB::raw('SUM(s.platform_compensations) + SUM(s.positive_bonus)'), '>', 0)
            ->orderByDesc('bonus')
            ->take(10)->get();

        // Fuel/violation/advance from specialist tables
        $fuelTotal    = (float) DB::table('fuel_entries')->sum('amount_sar');
        $violTotal    = (float) DB::table('violation_entries')->sum('amount');
        $advanceTotal = (float) DB::table('advance_entries')->sum('amount');

        // Monthly fuel trend
        $fuelMonthly = DB::table('fuel_entries as fe')
            ->join('monthly_periods as mp', 'mp.id', '=', 'fe.monthly_period_id')
            ->selectRaw('mp.label, SUM(fe.amount_sar) as total')
            ->groupByRaw('mp.id, mp.label')
            ->orderByRaw('mp.year, mp.month')
            ->get();

        // Top fuel consumers
        $topFuelDrivers = DB::table('fuel_entries as fe')
            ->join('delegates as d', 'd.id', '=', 'fe.delegate_id')
            ->selectRaw('d.name, SUM(fe.amount_sar) as total')
            ->groupBy('d.id', 'd.name')
            ->orderByDesc('total')->take(10)->get();

        // Monthly violations trend
        $violMonthly = DB::table('violation_entries as ve')
            ->join('monthly_periods as mp', 'mp.id', '=', 've.monthly_period_id')
            ->selectRaw('mp.label, SUM(ve.amount) as total, COUNT(*) as cnt')
            ->groupByRaw('mp.id, mp.label')
            ->orderByRaw('mp.year, mp.month')
            ->get();

        // Top violation drivers
        $topViolDrivers = DB::table('violation_entries as ve')
            ->join('delegates as d', 'd.id', '=', 've.delegate_id')
            ->selectRaw('d.name, SUM(ve.amount) as total, COUNT(*) as cnt')
            ->groupBy('d.id', 'd.name')
            ->orderByDesc('total')->take(10)->get();

        return compact(
            'hsBenefits', 'czBenefits',
            'hsBenefitsByDriver', 'czBenefitsByDriver',
            'fuelTotal', 'violTotal', 'advanceTotal',
            'fuelMonthly', 'topFuelDrivers',
            'violMonthly', 'topViolDrivers'
        );
    }

    // ── Section: Monthly Trend ────────────────────────────────────────────────

    private function buildMonthlyTrend(): array
    {
        $periods = DB::table('monthly_periods')
            ->orderByRaw('year, month')
            ->get();

        if ($periods->isEmpty()) {
            return ['labels' => [], 'hsRevenue' => [], 'czProfit' => [], 'driverPay' => [], 'combinedProfit' => []];
        }

        $pids = $periods->pluck('id');

        $hsAgg = DB::table('hungerstation_ftr_settlements')
            ->whereIn('monthly_period_id', $pids)
            ->selectRaw('monthly_period_id, SUM(basic_payment) as revenue, SUM(net_salary) as driver_pay, SUM(total_orders) as orders')
            ->groupBy('monthly_period_id')
            ->get()->keyBy('monthly_period_id');

        $czAgg = DB::table('chefz_delegate_settlements')
            ->whereIn('monthly_period_id', $pids)
            ->selectRaw('monthly_period_id, SUM(company_share_amount) as profit, SUM(net_salary) as driver_pay, SUM(total_orders) as orders')
            ->groupBy('monthly_period_id')
            ->get()->keyBy('monthly_period_id');

        $expAgg = DB::table('company_expenses')
            ->whereIn('monthly_period_id', $pids)
            ->selectRaw('monthly_period_id, SUM(amount) as total')
            ->groupBy('monthly_period_id')
            ->get()->keyBy('monthly_period_id');

        $labels = $hsRevenue = $czProfit = $driverPay = $combinedProfit = $orders = [];

        foreach ($periods as $p) {
            $hs  = $hsAgg[$p->id]  ?? null;
            $cz  = $czAgg[$p->id]  ?? null;
            $exp = $expAgg[$p->id] ?? null;

            $rev    = $hs ? (float)$hs->revenue : 0;
            $czP    = $cz ? (float)$cz->profit  : 0;
            $expT   = $exp ? (float)$exp->total  : 0;
            $pay    = ($hs ? (float)$hs->driver_pay : 0) + ($cz ? (float)$cz->driver_pay : 0);

            $labels[]         = $p->label;
            $hsRevenue[]      = round($rev, 2);
            $czProfit[]       = round($czP, 2);
            $driverPay[]      = round($pay, 2);
            $combinedProfit[] = round($rev - $expT + $czP, 2);
            $orders[]         = (int)($hs ? $hs->orders : 0) + (int)($cz ? $cz->orders : 0);
        }

        return compact('labels', 'hsRevenue', 'czProfit', 'driverPay', 'combinedProfit', 'orders');
    }

    // ── Section: Executive Insights ───────────────────────────────────────────

    private function buildInsights(array $overview, array $pa, array $dr): array
    {
        $insights = [];

        // Highest profit platform
        if ($overview['hsProfit'] > $overview['czProfit']) {
            $insights[] = ['icon'=>'🏆','color'=>'#16a34a','title'=>'أعلى منصة ربحاً','body'=>'هنقرستيشن بربح '.number_format($overview['hsProfit'], 2).' ريال'];
        } else {
            $insights[] = ['icon'=>'🏆','color'=>'#16a34a','title'=>'أعلى منصة ربحاً','body'=>'شيفز بربح '.number_format($overview['czProfit'], 2).' ريال'];
        }

        // Top HS driver by salary
        if ($dr['hsTopSalary']->isNotEmpty()) {
            $top = $dr['hsTopSalary']->first();
            $insights[] = ['icon'=>'⭐','color'=>'#d97706','title'=>'أعلى مندوب (هنقرستيشن)','body'=>$top->name.' — '.number_format($top->val, 2).' ريال'];
        }

        // Top Chefz driver by salary
        if ($dr['czTopSalary']->isNotEmpty()) {
            $top = $dr['czTopSalary']->first();
            $insights[] = ['icon'=>'⭐','color'=>'#0891b2','title'=>'أعلى مندوب (شيفز)','body'=>$top->name.' — '.number_format($top->val, 2).' ريال'];
        }

        // Avg profit per order
        $insights[] = ['icon'=>'📦','color'=>'#6366f1','title'=>'متوسط الربح للطلب','body'=>number_format($overview['avgProfitPerOrder'], 2).' ريال لكل طلب'];

        // Avg driver salary
        $insights[] = ['icon'=>'💵','color'=>'#7c3aed','title'=>'متوسط راتب المندوب','body'=>number_format($overview['avgDriverSalary'], 2).' ريال شهرياً'];

        // Platform contribution
        $insights[] = ['icon'=>'📊','color'=>'#0d9488','title'=>'مساهمة هنقرستيشن','body'=>$overview['hsContrib'].'% من إجمالي الربح'];
        $insights[] = ['icon'=>'📊','color'=>'#be185d','title'=>'مساهمة شيفز','body'=>$overview['czContrib'].'% من إجمالي الربح'];

        // HS orders vs Chefz orders
        $hsOrds = (int)$overview['hs']->orders;
        $czOrds = (int)$overview['cz']->orders;
        $insights[] = ['icon'=>'🛒','color'=>'#475569','title'=>'توزيع الطلبات','body'=>'HS: '.number_format($hsOrds).' طلب | شيفز: '.number_format($czOrds).' طلب'];

        // Chefz driver with most orders
        if ($dr['czTopOrders']->isNotEmpty()) {
            $top = $dr['czTopOrders']->first();
            $insights[] = ['icon'=>'🚀','color'=>'#ea580c','title'=>'أكثر مندوب توصيلاً (شيفز)','body'=>$top->name.' — '.number_format($top->val).' طلب'];
        }

        return $insights;
    }

    // ── Per-driver data ───────────────────────────────────────────────────────

    private function buildDriverData(Delegate $delegate): array
    {
        $did = $delegate->id;

        // HS settlements for this driver
        $hsSettlements = DB::table('hungerstation_ftr_settlements as s')
            ->join('monthly_periods as mp', 'mp.id', '=', 's.monthly_period_id')
            ->where('s.delegate_id', $did)
            ->selectRaw("mp.label, s.total_orders, s.basic_payment, s.distance_payment, s.net_salary,
                s.total_platform_penalties, s.housing_allowance, s.company_benefits_total, s.company_deductions_total")
            ->orderByRaw('mp.year, mp.month')
            ->get();

        // Chefz settlements for this driver
        $czSettlements = DB::table('chefz_delegate_settlements as s')
            ->join('monthly_periods as mp', 'mp.id', '=', 's.monthly_period_id')
            ->where('s.delegate_id', $did)
            ->selectRaw("mp.label, s.payout_number, s.total_orders, s.gross_delivery_fees,
                s.company_share_amount, s.net_salary, s.platform_compensations, s.positive_bonus,
                s.platform_deductions, s.chefz_tax_amount, s.commission_total, s.deductions_total")
            ->orderByRaw('mp.year, mp.month, s.payout_number')
            ->get();

        // Manual deductions (HS)
        $hsDeductions = DB::table('hungerstation_ftr_delegate_deductions as dd')
            ->leftJoin('monthly_periods as mp', 'mp.id', '=', 'dd.monthly_period_id')
            ->where('dd.delegate_id', $did)
            ->selectRaw("COALESCE(mp.label,'—') as label, dd.deduction_type, dd.is_benefit, dd.amount, dd.notes")
            ->orderByRaw('mp.year, mp.month')
            ->get();

        // Chefz manual deductions
        $czDeductions = DB::table('chefz_delegate_deductions')
            ->where('delegate_id', $did)
            ->selectRaw("amount, deduction_type, notes")
            ->get();

        // Fuel entries
        $fuelEntries = DB::table('fuel_entries as fe')
            ->join('monthly_periods as mp', 'mp.id', '=', 'fe.monthly_period_id')
            ->where('fe.delegate_id', $did)
            ->selectRaw("mp.label, fe.amount_sar as amount, fe.notes")
            ->orderByRaw('mp.year, mp.month')
            ->get();

        // Violation entries
        $violEntries = DB::table('violation_entries as ve')
            ->join('monthly_periods as mp', 'mp.id', '=', 've.monthly_period_id')
            ->where('ve.delegate_id', $did)
            ->selectRaw("mp.label, ve.amount, ve.notes")
            ->orderByRaw('mp.year, mp.month')
            ->get();

        $typeLabels = HungerStationFtrDelegateDeduction::TYPE_LABELS;

        return compact(
            'delegate', 'hsSettlements', 'czSettlements',
            'hsDeductions', 'czDeductions',
            'fuelEntries', 'violEntries', 'typeLabels'
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getPeriods(): \Illuminate\Support\Collection
    {
        return DB::table('monthly_periods as mp')
            ->join('platforms as p', 'p.id', '=', 'mp.platform_id')
            ->selectRaw('mp.id, mp.label, mp.year, mp.month, p.name as platform_name, p.code as platform_code')
            ->orderByRaw('mp.year, mp.month')
            ->get();
    }
}
