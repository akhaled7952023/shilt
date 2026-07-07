<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function index(Request $request): View
    {
        $allPeriods = $this->allPeriods();

        // Build available years from DB periods, extend to current year if needed
        $availableYears = $allPeriods->pluck('year')->unique()->sort()->values()->toArray();
        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        } else {
            $current = (int) date('Y');
            if (!in_array($current, $availableYears)) {
                $availableYears[] = $current;
                sort($availableYears);
            }
        }

        $aParams   = (array) $request->get('a', []);
        $bParams   = (array) $request->get('b', []);
        $aPlatform = $aParams['platform'] ?? 'all';
        $bPlatform = $bParams['platform'] ?? 'all';

        $aMonths = $this->resolveMonthKeys($aParams);
        $bMonths = $this->resolveMonthKeys($bParams);

        $groupA = $groupB = null;
        $diff   = [];

        if (!empty($aMonths)) {
            $groupA            = $this->aggregateMonths($aMonths, $allPeriods, $aPlatform);
            $groupA['label']   = $this->buildGroupLabel($aParams, $aPlatform);
        }
        if (!empty($bMonths)) {
            $groupB            = $this->aggregateMonths($bMonths, $allPeriods, $bPlatform);
            $groupB['label']   = $this->buildGroupLabel($bParams, $bPlatform);
        }
        if ($groupA && $groupB) {
            $diff = $this->computeDiff($groupA, $groupB);
        }

        // Which platform detail charts to show
        $showHs = ($aPlatform !== 'cz' || $bPlatform !== 'cz');
        $showCz = ($aPlatform !== 'hs' || $bPlatform !== 'hs');

        return view('dashboard.reports.comparison', compact(
            'allPeriods', 'availableYears',
            'aParams', 'bParams', 'aPlatform', 'bPlatform',
            'aMonths', 'bMonths',
            'groupA', 'groupB', 'diff',
            'showHs', 'showCz'
        ));
    }

    // ── Resolve month-key array from structured params ────────────────────────

    private function resolveMonthKeys(array $params): array
    {
        $type = $params['type'] ?? 'single';
        $year = (int) ($params['year'] ?? 0);
        if (!$year) return [];

        if ($type === 'single') {
            $month = (int) ($params['month'] ?? 0);
            if ($month < 1 || $month > 12) return [];
            return [sprintf('%04d-%02d', $year, $month)];
        }

        // range — same year, from → to
        $from = (int) ($params['from_month'] ?? 0);
        $to   = (int) ($params['to_month']   ?? 0);
        if ($from < 1 || $to < 1 || $from > 12 || $to > 12) return [];
        $keys = [];
        for ($m = min($from, $to); $m <= max($from, $to); $m++) {
            $keys[] = sprintf('%04d-%02d', $year, $m);
        }
        return $keys;
    }

    // ── Build a human-readable label from structured params ───────────────────

    private function buildGroupLabel(array $params, string $platform): string
    {
        $ar = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $platLabel = match ($platform) {
            'hs'    => 'هنقرستيشن',
            'cz'    => 'شيفز',
            default => 'الكل',
        };

        $year = (int) ($params['year'] ?? 0);
        $type = $params['type'] ?? 'single';

        if (!$year) return $platLabel;

        if ($type === 'single') {
            $m = (int) ($params['month'] ?? 0);
            $mLabel = ($m >= 1 && $m <= 12) ? $ar[$m] : '—';
            return "$mLabel $year — $platLabel";
        }

        $from = (int) ($params['from_month'] ?? 0);
        $to   = (int) ($params['to_month']   ?? 0);
        $fLabel = ($from >= 1 && $from <= 12) ? $ar[$from] : '—';
        $tLabel = ($to   >= 1 && $to   <= 12) ? $ar[$to]   : '—';
        return $fLabel . '–' . $tLabel . ' ' . $year . ' — ' . $platLabel;
    }

    // ── All periods (with platform code) ─────────────────────────────────────

    private function allPeriods(): \Illuminate\Support\Collection
    {
        return DB::table('monthly_periods as mp')
            ->join('platforms as p', 'p.id', '=', 'mp.platform_id')
            ->selectRaw('mp.id, mp.label, mp.year, mp.month, mp.platform_id,
                         p.name as platform_name, p.code as platform_code, mp.status')
            ->orderByRaw('mp.year, mp.month, mp.platform_id')
            ->get();
    }

    // ── Aggregate finalized records for a set of month keys ──────────────────

    private function aggregateMonths(array $monthKeys, \Illuminate\Support\Collection $allPeriods, string $platform): array
    {
        $monthKeys = array_values(array_unique(array_filter($monthKeys)));
        if (empty($monthKeys)) return [];

        // Resolve HS period IDs
        $hsIds = ($platform !== 'cz')
            ? $allPeriods->filter(fn($p) => $p->platform_code === 'hungerstation' &&
                in_array($p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT), $monthKeys))
                ->pluck('id')->toArray()
            : [];

        // Resolve Chefz period IDs
        $czIds = ($platform !== 'hs')
            ? $allPeriods->filter(fn($p) => $p->platform_code === 'the-chefz' &&
                in_array($p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT), $monthKeys))
                ->pluck('id')->toArray()
            : [];

        $hs = $this->aggregateHs($hsIds);
        $cz = $this->aggregateCz($czIds);

        $manualDed = empty($hsIds) ? 0.0 :
            (float) DB::table('hungerstation_ftr_delegate_deductions')
                ->whereIn('monthly_period_id', $hsIds)->where('is_benefit', 0)->sum('amount');

        $manualBen = empty($hsIds) ? 0.0 :
            (float) DB::table('hungerstation_ftr_delegate_deductions')
                ->whereIn('monthly_period_id', $hsIds)->where('is_benefit', 1)->sum('amount');

        $allIds  = array_merge($hsIds, $czIds);
        $compExp = empty($allIds) ? 0.0 :
            (float) DB::table('company_expenses')->whereIn('monthly_period_id', $allIds)->sum('amount');

        $hsRevenue = $hs['revenue'];
        $hsProfit  = round($hsRevenue - $compExp, 2);
        $czProfit  = $cz['profit'];
        $combined  = round($hsProfit + $czProfit, 2);

        $totalOrders    = $hs['orders'] + $cz['orders'];
        $totalDriverPay = round($hs['driver_pay'] + $cz['driver_pay'], 2);
        $totalDrivers   = max(1, $hs['drivers'] + $cz['drivers']);
        $hsBenefits     = round($hs['housing'] + $hs['benefits'], 2);

        return [
            'label'               => '', // overwritten by caller
            'month_keys'          => $monthKeys,
            'orders'              => $totalOrders,
            'revenue'             => round($hsRevenue + $czProfit, 2),
            'hs_revenue'          => $hsRevenue,
            'cz_gross'            => $cz['gross'],
            'driver_pay'          => $totalDriverPay,
            'hs_driver_pay'       => $hs['driver_pay'],
            'cz_driver_pay'       => $cz['driver_pay'],
            'hs_profit'           => $hsProfit,
            'cz_profit'           => $czProfit,
            'combined_profit'     => $combined,
            'comp_exp'            => $compExp,
            'platform_deductions' => round($hs['platform_deductions'] + $cz['platform_deductions'], 2),
            'compensations'       => $cz['compensations'],
            'bonuses'             => $cz['bonuses'],
            'benefits'            => $hsBenefits,
            'manual_deductions'   => $manualDed,
            'manual_benefits'     => $manualBen,
            'vat'                 => $cz['vat'],
            'avg_salary'          => round($totalDriverPay / $totalDrivers, 2),
            'avg_orders'          => round($totalOrders / max(1, $totalDrivers), 2),
            'drivers'             => $totalDrivers,
            'hs_orders'           => $hs['orders'],
            'cz_orders'           => $cz['orders'],
        ];
    }

    private function aggregateHs(array $ids): array
    {
        if (empty($ids)) {
            return ['revenue'=>0,'driver_pay'=>0,'orders'=>0,'platform_deductions'=>0,
                    'housing'=>0,'benefits'=>0,'company_deductions'=>0,'drivers'=>0];
        }
        $row = DB::table('hungerstation_ftr_settlements')
            ->whereIn('monthly_period_id', $ids)
            ->selectRaw("
                COALESCE(SUM(basic_payment), 0)            as revenue,
                COALESCE(SUM(net_salary), 0)               as driver_pay,
                COALESCE(SUM(total_orders), 0)             as orders,
                COALESCE(SUM(total_platform_penalties), 0) as platform_deductions,
                COALESCE(SUM(housing_allowance), 0)        as housing,
                COALESCE(SUM(company_benefits_total), 0)   as benefits,
                COALESCE(SUM(company_deductions_total), 0) as company_deductions,
                COUNT(DISTINCT delegate_id)                as drivers
            ")->first();
        return [
            'revenue'             => (float) $row->revenue,
            'driver_pay'          => (float) $row->driver_pay,
            'orders'              => (int)   $row->orders,
            'platform_deductions' => (float) $row->platform_deductions,
            'housing'             => (float) $row->housing,
            'benefits'            => (float) $row->benefits,
            'company_deductions'  => (float) $row->company_deductions,
            'drivers'             => (int)   $row->drivers,
        ];
    }

    private function aggregateCz(array $ids): array
    {
        if (empty($ids)) {
            return ['gross'=>0,'profit'=>0,'driver_pay'=>0,'orders'=>0,
                    'platform_deductions'=>0,'compensations'=>0,'bonuses'=>0,'vat'=>0,'drivers'=>0];
        }
        $row = DB::table('chefz_delegate_settlements')
            ->whereIn('monthly_period_id', $ids)
            ->selectRaw("
                COALESCE(SUM(gross_delivery_fees), 0)    as gross,
                COALESCE(SUM(company_share_amount), 0)   as profit,
                COALESCE(SUM(net_salary), 0)             as driver_pay,
                COALESCE(SUM(total_orders), 0)           as orders,
                COALESCE(SUM(platform_compensations), 0) as compensations,
                COALESCE(SUM(platform_deductions), 0)    as platform_deductions,
                COALESCE(SUM(positive_bonus), 0)         as bonuses,
                COALESCE(SUM(chefz_tax_amount), 0)       as vat,
                COUNT(DISTINCT delegate_id)              as drivers
            ")->first();
        return [
            'gross'               => (float) $row->gross,
            'profit'              => (float) $row->profit,
            'driver_pay'          => (float) $row->driver_pay,
            'orders'              => (int)   $row->orders,
            'platform_deductions' => (float) $row->platform_deductions,
            'compensations'       => (float) $row->compensations,
            'bonuses'             => (float) $row->bonuses,
            'vat'                 => (float) $row->vat,
            'drivers'             => (int)   $row->drivers,
        ];
    }

    // ── Compute diff A → B ───────────────────────────────────────────────────

    private function computeDiff(array $a, array $b): array
    {
        $metrics = [
            'orders', 'revenue', 'driver_pay', 'combined_profit',
            'platform_deductions', 'compensations', 'benefits',
            'manual_deductions', 'avg_salary', 'avg_orders',
        ];
        $diff = [];
        foreach ($metrics as $key) {
            $va    = (float) ($a[$key] ?? 0);
            $vb    = (float) ($b[$key] ?? 0);
            $delta = round($vb - $va, 2);
            $pct   = $va != 0 ? round(($vb - $va) / abs($va) * 100, 1) : null;
            $diff[$key] = ['a'=>$va, 'b'=>$vb, 'delta'=>$delta, 'pct'=>$pct, 'up'=>$delta >= 0];
        }
        return $diff;
    }
}
