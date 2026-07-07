<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Http\Controllers\Controller;
use App\Models\HungerStationFtrDelegateDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExecutiveDashboardController extends Controller
{
    // ── Entry points ─────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $data    = $this->buildData($filters);
        $regions = collect(); // FTR has no per-order region data
        return view('dashboard.reports.executive', $data + compact('filters', 'regions'));
    }

    public function pdf(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $data    = $this->buildData($filters);
        return view('dashboard.reports.executive_pdf', $data + compact('filters'));
    }

    // ── Filter resolution ─────────────────────────────────────────────────────────

    private function resolveFilters(Request $request): array
    {
        if ($q = $request->input('quick')) {
            [$from, $to] = match ($q) {
                'today'      => [today()->toDateString(), today()->toDateString()],
                'week'       => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
                'month'      => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                'last_month' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                'last_3'     => [now()->subMonths(3)->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                'last_6'     => [now()->subMonths(6)->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
                'year'       => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
                default      => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            };
        } elseif ($request->filled('year') && $request->filled('month')) {
            $d    = Carbon::create((int) $request->year, (int) $request->month, 1);
            $from = $d->copy()->startOfMonth()->toDateString();
            $to   = $d->copy()->endOfMonth()->toDateString();
        } elseif ($request->filled('date_from') && $request->filled('date_to')) {
            $from = Carbon::parse($request->date_from)->toDateString();
            $to   = Carbon::parse($request->date_to)->toDateString();
        } else {
            $from = now()->startOfMonth()->toDateString();
            $to   = now()->endOfMonth()->toDateString();
        }

        return [
            'date_from' => $from,
            'date_to'   => $to,
            'platform'  => $request->input('platform', 'all'),
            'driver_id' => $request->input('driver_id'),
            'region'    => $request->input('region'),
            'quick'     => $request->input('quick'),
            'year'      => $request->input('year'),
            'month'     => $request->input('month'),
            'sort'      => $request->input('sort', 'salary_desc'),
            'page'      => max(1, (int) $request->input('page', 1)),
        ];
    }

    // ── Master data builder ───────────────────────────────────────────────────────

    private function buildData(array $f): array
    {
        $includeHs    = in_array($f['platform'], ['all', 'hungerstation']);
        $includeChefz = in_array($f['platform'], ['all', 'the-chefz']);

        $hsPeriodIds    = $includeHs    ? $this->getPeriodIds($f['date_from'], $f['date_to'], 'hungerstation') : collect();
        $chefzPeriodIds = $includeChefz ? $this->getPeriodIds($f['date_from'], $f['date_to'], 'the-chefz')    : collect();
        $periodIds      = $hsPeriodIds->merge($chefzPeriodIds)->unique()->values();

        $delegateId = $this->resolveDelegateId($f['driver_id'], $includeHs, $includeChefz);

        // ── Raw aggregates ──
        $hsSett      = $includeHs    ? $this->hsSettlementAgg($hsPeriodIds, $delegateId) : null;
        $chefzOrders = $includeChefz ? $this->chefzOrderAgg($f, $delegateId) : null;
        $chefzSett   = $includeChefz ? $this->chefzSettlementAgg($chefzPeriodIds, $delegateId) : null;
        $hsManDed    = $includeHs    ? $this->hsManualDeductionsAgg($hsPeriodIds, $delegateId) : 0;
        $compExp     = $includeHs    ? (float) DB::table('company_expenses')->whereIn('monthly_period_id', $hsPeriodIds)->sum('amount') : 0;

        // ── KPIs ──
        $kpis = $this->buildKpis($hsSett, $chefzOrders, $chefzSett, $hsManDed, $compExp, $includeHs, $includeChefz);

        // ── Charts ──
        $dailyData          = $this->buildDailyTrend($f, $delegateId, $includeHs, $includeChefz);
        $monthlyTrend       = $this->buildMonthlyTrend();
        $topByOrders        = $this->buildTopByOrders($hsPeriodIds, $chefzPeriodIds, $f, $delegateId, $includeHs, $includeChefz);
        $topBySalary        = $this->buildTopBySalary($hsPeriodIds, $chefzPeriodIds, $delegateId, $includeHs, $includeChefz);
        $deductionsByType   = $includeHs ? $this->buildDeductionsByType($hsPeriodIds, $delegateId) : collect();
        $expensesByCategory = $includeHs ? $this->buildExpensesByCategory($hsPeriodIds) : collect();
        $platformCompare    = $this->buildPlatformCompare($hsSett, $chefzOrders, $chefzSett, $includeHs, $includeChefz);
        $regionData         = collect(); // FTR has no per-order region data

        // ── Detail table ──
        $table = $this->buildDetailTable($f, $hsPeriodIds, $chefzPeriodIds, $delegateId, $includeHs, $includeChefz);

        return compact(
            'kpis', 'dailyData', 'monthlyTrend',
            'topByOrders', 'topBySalary',
            'deductionsByType', 'expensesByCategory',
            'platformCompare', 'regionData',
            'table', 'periodIds',
            'compExp', 'hsManDed'
        );
    }

    // ── Helpers: Period IDs ───────────────────────────────────────────────────────

    private function getPeriodIds(string $from, string $to, ?string $platformCode = null): \Illuminate\Support\Collection
    {
        $q = DB::table('monthly_periods')
            ->whereRaw("STR_TO_DATE(CONCAT(year,'-',LPAD(month,2,'0'),'-01'),'%Y-%m-%d') <= ?", [$to])
            ->whereRaw("LAST_DAY(STR_TO_DATE(CONCAT(year,'-',LPAD(month,2,'0'),'-01'),'%Y-%m-%d')) >= ?", [$from]);

        if ($platformCode) {
            $platformId = DB::table('platforms')->where('code', $platformCode)->value('id');
            if ($platformId) {
                $q->where('platform_id', $platformId);
            }
        }

        return $q->pluck('id');
    }

    private function resolveDelegateId(?string $rawDriverId, bool $hs, bool $chefz): ?int
    {
        if (! $rawDriverId) return null;

        // For FTR: look up by hungerstation_rider_id
        if ($hs) {
            $id = DB::table('delegates')
                ->where('hungerstation_rider_id', $rawDriverId)
                ->value('id');
            if ($id) return $id;

            // Fallback: check FTR settlements by rider_id_platform
            $id = DB::table('hungerstation_ftr_settlements')
                ->where('rider_id_platform', $rawDriverId)
                ->value('delegate_id');
            if ($id) return $id;
        }

        if ($chefz) {
            $id = DB::table('chefz_orders')
                ->where('raw_driver_id', $rawDriverId)
                ->value('delegate_id');
            if ($id) return $id;
        }

        return -1;
    }

    // ── Helpers: Aggregates ───────────────────────────────────────────────────────

    /**
     * Aggregate FTR settlement data for HS KPIs.
     * basic_payment = company revenue from HS (equivalent to gross_fees in commission model)
     * total_platform_penalties = platform deductions
     * distance_payment = base delegate gross pay
     */
    private function hsSettlementAgg(\Illuminate\Support\Collection $periodIds, ?int $delegateId): object
    {
        return DB::table('hungerstation_ftr_settlements')
            ->whereIn('monthly_period_id', $periodIds)
            ->when($delegateId, fn($q) => $q->where('delegate_id', $delegateId))
            ->selectRaw("
                COALESCE(SUM(basic_payment),0)             as gross_fees,
                COALESCE(SUM(total_platform_penalties),0)  as platform_deductions,
                0                                          as platform_comps,
                COALESCE(SUM(housing_allowance),0)         as housing,
                0                                          as entitlements,
                0                                          as grants,
                COALESCE(SUM(net_salary),0)                as net_salaries,
                COALESCE(SUM(total_orders),0)              as total_orders,
                COUNT(*)                                   as driver_count,
                COALESCE(SUM(distance_payment),0)          as distance_payment,
                COALESCE(SUM(stacking_deduction),0)        as stacking_deduction,
                COALESCE(SUM(rider_balance),0)             as rider_balance
            ")->first();
    }

    private function chefzOrderAgg(array $f, ?int $delegateId): object
    {
        return DB::table('chefz_orders')
            ->whereBetween('order_date', [$f['date_from'], $f['date_to']])
            ->when($delegateId, fn($q) => $q->where('delegate_id', $delegateId))
            ->selectRaw("
                COUNT(*) as total_orders,
                COUNT(DISTINCT delegate_id) as driver_count,
                COALESCE(SUM(delivery_fee),0) as gross_fees,
                COALESCE(SUM(deduction_amount),0) as platform_deductions,
                COALESCE(SUM(compensation),0) as platform_comps,
                0 as company_commission
            ")->first();
    }

    private function chefzSettlementAgg(\Illuminate\Support\Collection $periodIds, ?int $delegateId): object
    {
        return DB::table('chefz_delegate_settlements')
            ->whereIn('monthly_period_id', $periodIds)
            ->when($delegateId, fn($q) => $q->where('delegate_id', $delegateId))
            ->selectRaw("
                COALESCE(SUM(gross_delivery_fees),0) as gross_fees,
                COALESCE(SUM(chefz_tax_amount),0) as vat_total,
                COALESCE(SUM(company_share_amount),0) as company_share,
                COALESCE(SUM(net_salary),0) as net_salaries,
                COALESCE(SUM(total_orders),0) as total_orders,
                COUNT(*) as driver_count
            ")->first();
    }

    private function hsManualDeductionsAgg(\Illuminate\Support\Collection $periodIds, ?int $delegateId): float
    {
        return (float) DB::table('hungerstation_ftr_delegate_deductions')
            ->whereIn('monthly_period_id', $periodIds)
            ->when($delegateId, fn($q) => $q->where('delegate_id', $delegateId))
            ->sum('amount');
    }

    // ── KPI builder ───────────────────────────────────────────────────────────────

    private function buildKpis(
        ?object $hsS, ?object $czO,
        ?object $czS,
        float $hsManDed, float $compExp,
        bool $inclHs, bool $inclCz
    ): array {
        // FTR: total_orders from settlements (no per-order table for HS)
        $totalOrders   = (int) (($inclHs ? ($hsS->total_orders ?? 0) : 0) + ($inclCz ? ($czO->total_orders ?? 0) : 0));
        $totalDrivers  = (int) (($inclHs ? ($hsS->driver_count ?? 0) : 0) + ($inclCz ? ($czS->driver_count ?? 0) : 0));
        $grossFees     = (float) (($inclHs ? ($hsS->gross_fees ?? 0) : 0) + ($inclCz ? ($czS->gross_fees ?? 0) : 0));
        $platDed       = (float) ($inclHs ? ($hsS->platform_deductions ?? 0) : 0);
        $platComp      = 0.0; // FTR has no platform compensations
        $vatTotal      = (float) ($inclCz ? ($czS->vat_total ?? 0) : 0);
        $companyShare  = (float) ($inclCz ? ($czS->company_share ?? 0) : 0);
        $totalSalaries = (float) (($inclHs ? ($hsS->net_salaries ?? 0) : 0) + ($inclCz ? ($czS->net_salaries ?? 0) : 0));

        // ── Per-platform profits read directly from finalized settlement columns ──
        //
        // HS profit:    basic_payment − company_expenses
        //               (consistent with MonthlyFinancialDashboardController::buildHsData)
        // Chefz profit: company_share_amount
        //               (from settlement engine — never recalculated here)
        $hsRevenue = (float) ($inclHs ? ($hsS->gross_fees ?? 0) : 0); // gross_fees = basic_payment in FTR
        $hsProfit  = $inclHs ? round($hsRevenue - $compExp, 2) : 0.0;
        $czProfit  = $inclCz ? round($companyShare, 2) : 0.0;

        // ── Combined company totals ──
        // Net revenue = what flows into the company: HS basic_payment + Chefz company_share
        // Net profit  = HS profit + Chefz profit
        $netRevenue = round($hsRevenue + $czProfit, 2);
        $netProfit  = round($hsProfit  + $czProfit, 2);

        $avgOrders = $totalDrivers > 0 ? round($totalOrders / $totalDrivers, 1) : 0;
        $avgSalary = $totalDrivers > 0 ? round($totalSalaries / $totalDrivers, 2) : 0;
        $avgProfit = $totalDrivers > 0 ? round($netProfit / $totalDrivers, 2) : 0;

        return compact(
            'totalOrders', 'totalDrivers', 'grossFees', 'platDed', 'platComp',
            'vatTotal', 'companyShare', 'totalSalaries', 'compExp',
            'hsManDed', 'netRevenue', 'netProfit',
            'hsProfit', 'czProfit',
            'avgOrders', 'avgSalary', 'avgProfit'
        );
    }

    // ── Chart builders ────────────────────────────────────────────────────────────

    private function buildDailyTrend(array $f, ?int $delegateId, bool $inclHs, bool $inclCz): array
    {
        $start  = Carbon::parse($f['date_from']);
        $end    = Carbon::parse($f['date_to']);
        $labels = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $labels[] = $d->toDateString();
        }

        // FTR has no per-day order data — HS daily chart is empty
        $czDaily = $inclCz
            ? DB::table('chefz_orders')
                ->whereBetween('order_date', [$f['date_from'], $f['date_to']])
                ->when($delegateId, fn($q) => $q->where('delegate_id', $delegateId))
                ->selectRaw("DATE(order_date) as d, COUNT(*) as orders, COALESCE(SUM(delivery_fee),0) as rev")
                ->groupByRaw('DATE(order_date)')
                ->get()->keyBy('d')
            : collect();

        if (count($labels) > 60) {
            $step = ceil(count($labels) / 60);
            $labels = array_values(array_filter($labels, fn($_, $i) => $i % $step === 0, ARRAY_FILTER_USE_BOTH));
        }

        $hsOrders = $hsFees = $czOrders = $czFees = [];
        foreach ($labels as $date) {
            $hsOrders[] = 0; // no per-day data for FTR
            $hsFees[]   = 0;
            $czOrders[] = (int)   ($czDaily[$date]->orders ?? 0);
            $czFees[]   = (float) ($czDaily[$date]->rev    ?? 0);
        }

        return compact('labels', 'hsOrders', 'hsFees', 'czOrders', 'czFees');
    }

    private function buildMonthlyTrend(): array
    {
        $periods = DB::table('monthly_periods')
            ->where('status', 'closed')
            ->orderByDesc('year')->orderByDesc('month')
            ->take(12)->get();

        if ($periods->isEmpty()) return ['labels' => [], 'revenue' => [], 'salaries' => [], 'profit' => []];

        $pids = $periods->pluck('id');

        // FTR: use basic_payment as HS revenue
        $hsAgg = DB::table('hungerstation_ftr_settlements')
            ->whereIn('monthly_period_id', $pids)
            ->selectRaw('monthly_period_id, SUM(basic_payment) as fees, 0 as deds, 0 as comps, SUM(net_salary) as sal')
            ->groupBy('monthly_period_id')->get()->keyBy('monthly_period_id');

        $czAgg = DB::table('chefz_delegate_settlements')
            ->whereIn('monthly_period_id', $pids)
            ->selectRaw('monthly_period_id, SUM(gross_delivery_fees) as fees, SUM(net_salary) as sal, SUM(company_share_amount) as share')
            ->groupBy('monthly_period_id')->get()->keyBy('monthly_period_id');

        $expAgg = DB::table('company_expenses')
            ->whereIn('monthly_period_id', $pids)
            ->selectRaw('monthly_period_id, SUM(amount) as total')
            ->groupBy('monthly_period_id')->get()->keyBy('monthly_period_id');

        $labels = $revenue = $salaries = $profit = [];

        foreach ($periods->reverse()->values() as $p) {
            $hs  = $hsAgg[$p->id]  ?? null;
            $cz  = $czAgg[$p->id]  ?? null;
            $exp = $expAgg[$p->id] ?? null;

            // FTR: fees = basic_payment (company revenue)
            $hsRev = $hs ? (float) $hs->fees : 0;
            $czFee = $cz ? (float) $cz->fees : 0;
            $sal   = ($hs ? (float) $hs->sal : 0) + ($cz ? (float) $cz->sal : 0);
            $share = $cz ? (float) $cz->share : 0;
            $expT  = $exp ? (float) $exp->total : 0;

            $labels[]   = $p->label;
            $revenue[]  = round($hsRev + $czFee, 2);
            $salaries[] = round($sal, 2);
            // profit = HS profit + Chefz profit (consistent with monthly dashboard definitions)
            // HS profit  = basic_payment - company_expenses   (no salary deduction)
            // Chefz profit = company_share_amount
            $profit[]   = round($hsRev - $expT + $share, 2);
        }

        return compact('labels', 'revenue', 'salaries', 'profit');
    }

    private function buildTopByOrders(
        \Illuminate\Support\Collection $hsPeriodIds,
        \Illuminate\Support\Collection $chefzPeriodIds,
        array $f,
        ?int $delegateId,
        bool $inclHs,
        bool $inclCz
    ): array {
        $rows = collect();

        // FTR: top by total_orders from settlements
        if ($inclHs && $hsPeriodIds->isNotEmpty()) {
            $hs = DB::table('hungerstation_ftr_settlements as hfs')
                ->join('delegates as d', 'd.id', '=', 'hfs.delegate_id')
                ->whereIn('hfs.monthly_period_id', $hsPeriodIds)
                ->when($delegateId, fn($q) => $q->where('hfs.delegate_id', $delegateId))
                ->selectRaw("d.name, SUM(hfs.total_orders) as orders, 'hungerstation' as platform")
                ->groupBy('hfs.delegate_id', 'd.name')
                ->orderByDesc('orders')
                ->take(10)->get();
            $rows = $rows->merge($hs);
        }

        if ($inclCz && $chefzPeriodIds->isNotEmpty()) {
            $cz = DB::table('chefz_orders as co')
                ->join('delegates as d', 'd.id', '=', 'co.delegate_id')
                ->whereBetween('co.order_date', [$f['date_from'], $f['date_to']])
                ->when($delegateId, fn($q) => $q->where('co.delegate_id', $delegateId))
                ->selectRaw("d.name, COUNT(*) as orders, 'chefz' as platform")
                ->groupBy('co.delegate_id', 'd.name')
                ->orderByDesc('orders')
                ->take(10)->get();
            $rows = $rows->merge($cz);
        }

        return $rows->sortByDesc('orders')->take(10)->values()->toArray();
    }

    private function buildTopBySalary(
        \Illuminate\Support\Collection $hsPeriodIds,
        \Illuminate\Support\Collection $chefzPeriodIds,
        ?int $delegateId,
        bool $inclHs,
        bool $inclCz
    ): array {
        $rows = collect();

        if ($inclHs && $hsPeriodIds->isNotEmpty()) {
            $hs = DB::table('hungerstation_ftr_settlements as hfs')
                ->join('delegates as d', 'd.id', '=', 'hfs.delegate_id')
                ->whereIn('hfs.monthly_period_id', $hsPeriodIds)
                ->when($delegateId, fn($q) => $q->where('hfs.delegate_id', $delegateId))
                ->selectRaw("d.name, SUM(hfs.net_salary) as salary, SUM(hfs.total_orders) as orders, 'hungerstation' as platform")
                ->groupBy('hfs.delegate_id', 'd.name')
                ->orderByDesc('salary')
                ->take(10)->get();
            $rows = $rows->merge($hs);
        }

        if ($inclCz && $chefzPeriodIds->isNotEmpty()) {
            $cz = DB::table('chefz_delegate_settlements as cds')
                ->join('delegates as d', 'd.id', '=', 'cds.delegate_id')
                ->whereIn('cds.monthly_period_id', $chefzPeriodIds)
                ->when($delegateId, fn($q) => $q->where('cds.delegate_id', $delegateId))
                ->selectRaw("d.name, SUM(cds.net_salary) as salary, SUM(cds.total_orders) as orders, 'chefz' as platform")
                ->groupBy('cds.delegate_id', 'd.name')
                ->orderByDesc('salary')
                ->take(10)->get();
            $rows = $rows->merge($cz);
        }

        return $rows->sortByDesc('salary')->take(10)->values()->toArray();
    }

    private function buildDeductionsByType(\Illuminate\Support\Collection $periodIds, ?int $delegateId): \Illuminate\Support\Collection
    {
        $typeLabels = HungerStationFtrDelegateDeduction::TYPE_LABELS;
        return DB::table('hungerstation_ftr_delegate_deductions')
            ->whereIn('monthly_period_id', $periodIds)
            ->when($delegateId, fn($q) => $q->where('delegate_id', $delegateId))
            ->selectRaw('deduction_type, SUM(amount) as total')
            ->groupBy('deduction_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn($d) => [
                'label' => $typeLabels[$d->deduction_type] ?? $d->deduction_type,
                'value' => round((float) $d->total, 2),
            ]);
    }

    private function buildExpensesByCategory(\Illuminate\Support\Collection $periodIds): \Illuminate\Support\Collection
    {
        return DB::table('company_expenses')
            ->whereIn('monthly_period_id', $periodIds)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => ['label' => $e->category, 'value' => round((float) $e->total, 2)]);
    }

    private function buildPlatformCompare(?object $hsS, ?object $czO, ?object $czS, bool $inclHs, bool $inclCz): array
    {
        // FTR: HS revenue = basic_payment (gross_fees in the hsS aggregate)
        $hsRev = $inclHs && $hsS ? round((float) $hsS->gross_fees, 2) : 0;
        return [
            'hs' => [
                'orders'  => $inclHs ? (int)   ($hsS->total_orders ?? 0) : 0,
                'revenue' => $hsRev,
                'salary'  => $inclHs ? round((float) ($hsS->net_salaries ?? 0), 2) : 0,
            ],
            'cz' => [
                'orders'  => $inclCz ? (int)   ($czO->total_orders ?? 0) : 0,
                'revenue' => $inclCz ? round((float) ($czS->gross_fees ?? 0), 2) : 0,
                'salary'  => $inclCz ? round((float) ($czS->net_salaries ?? 0), 2) : 0,
            ],
        ];
    }

    // ── Detail table ──────────────────────────────────────────────────────────────

    private function buildDetailTable(
        array $f,
        \Illuminate\Support\Collection $hsPeriodIds,
        \Illuminate\Support\Collection $chefzPeriodIds,
        ?int $delegateId,
        bool $inclHs,
        bool $inclCz
    ): \Illuminate\Support\Collection {
        $rows = collect();

        if ($inclHs && $hsPeriodIds->isNotEmpty()) {
            $hs = DB::table('hungerstation_ftr_settlements as hfs')
                ->join('delegates as d', 'd.id', '=', 'hfs.delegate_id')
                ->join('monthly_periods as mp', 'mp.id', '=', 'hfs.monthly_period_id')
                ->whereIn('hfs.monthly_period_id', $hsPeriodIds)
                ->when($delegateId, fn($q) => $q->where('hfs.delegate_id', $delegateId))
                ->selectRaw("
                    d.name,
                    hfs.rider_id_platform as driver_id,
                    '' as region,
                    'hungerstation' as platform,
                    mp.label as period,
                    hfs.total_orders as orders,
                    hfs.basic_payment as fees,
                    hfs.total_platform_penalties as deductions,
                    0 as comps,
                    0 as vat,
                    0 as company_share,
                    hfs.net_salary as salary
                ")->get();
            $rows = $rows->merge($hs);
        }

        if ($inclCz && $chefzPeriodIds->isNotEmpty()) {
            $cz = DB::table('chefz_delegate_settlements as cds')
                ->join('delegates as d', 'd.id', '=', 'cds.delegate_id')
                ->join('monthly_periods as mp', 'mp.id', '=', 'cds.monthly_period_id')
                ->leftJoin(
                    DB::raw("(SELECT delegate_id, monthly_period_id, raw_driver_id
                              FROM chefz_orders GROUP BY delegate_id, monthly_period_id, raw_driver_id) co"),
                    fn($j) => $j->on('co.delegate_id', '=', 'cds.delegate_id')
                               ->on('co.monthly_period_id', '=', 'cds.monthly_period_id')
                )
                ->whereIn('cds.monthly_period_id', $chefzPeriodIds)
                ->when($delegateId, fn($q) => $q->where('cds.delegate_id', $delegateId))
                ->selectRaw("
                    d.name,
                    COALESCE(co.raw_driver_id, '') as driver_id,
                    '' as region,
                    'the-chefz' as platform,
                    mp.label as period,
                    cds.total_orders as orders,
                    cds.gross_delivery_fees as fees,
                    0 as deductions,
                    0 as comps,
                    cds.chefz_tax_amount as vat,
                    cds.company_share_amount as company_share,
                    cds.net_salary as salary
                ")->get();
            $rows = $rows->merge($cz);
        }

        // Apply sort
        $rows = match($f['sort']) {
            'orders_desc' => $rows->sortByDesc('orders'),
            'orders_asc'  => $rows->sortBy('orders'),
            'salary_asc'  => $rows->sortBy('salary'),
            'fees_desc'   => $rows->sortByDesc('fees'),
            'fees_asc'    => $rows->sortBy('fees'),
            default       => $rows->sortByDesc('salary'),
        };

        return $rows->values();
    }
}
