<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index()
    {
        // ── Counts ──────────────────────────────────────────────────────────────
        $totalDelegates = DB::table('delegates')->whereNull('deleted_at')->count();
        $totalVehicles  = DB::table('vehicles')->whereNull('deleted_at')->count();

        // ── Orders & Revenue ─────────────────────────────────────────────────────
        $hsOrders  = (int)   DB::table('hungerstation_ftr_settlements')->sum('total_orders');
        $czOrders  = (int)   DB::table('chefz_delegate_settlements')->sum('total_orders');
        $totalOrders = $hsOrders + $czOrders;

        // HS profit = revenue - company operating expenses
        $hsRevenue = (float) DB::table('hungerstation_ftr_settlements')->sum('basic_payment');
        $compExp   = (float) DB::table('company_expenses')->sum('amount');
        $hsProfit  = round($hsRevenue - $compExp, 2);

        // Chefz profit = company_share_amount (finalized in settlement)
        $czProfit    = (float) DB::table('chefz_delegate_settlements')->sum('company_share_amount');
        $totalProfit = round($hsProfit + $czProfit, 2);

        // Avg profit per delegate (across delegates who have at least one settlement)
        $hsDelIds = DB::table('hungerstation_ftr_settlements')->distinct()->pluck('delegate_id')->toArray();
        $czDelIds = DB::table('chefz_delegate_settlements')->distinct()->pluck('delegate_id')->toArray();
        $activeDriverCount   = count(array_unique(array_merge($hsDelIds, $czDelIds)));
        $avgProfitPerDriver  = $activeDriverCount > 0 ? round($totalProfit / $activeDriverCount, 2) : 0;

        // ── Expenses summary ─────────────────────────────────────────────────────
        $expSalaries    = round(
            (float) DB::table('hungerstation_ftr_settlements')->sum('net_salary') +
            (float) DB::table('chefz_delegate_settlements')->sum('net_salary'), 2
        );
        $expHousing     = round(
            (float) DB::table('hungerstation_ftr_settlements')->sum('housing_allowance'), 2
        );
        $expBenefits    = round(
            (float) DB::table('hungerstation_ftr_settlements')->sum('company_benefits_total'), 2
        );
        $expFuel        = round((float) DB::table('fuel_entries')->sum('amount_sar'), 2);
        $expMaintenance = round((float) DB::table('vehicle_maintenance')->sum('cost'), 2);
        $expViolations  = round(
            (float) DB::table('violation_entries')->sum('amount') +
            (float) DB::table('vehicle_violations')->sum('amount'), 2
        );
        $expAdvances    = round((float) DB::table('advance_entries')->sum('amount'), 2);
        $expCompany     = round((float) DB::table('company_expenses')->sum('amount'), 2);

        $totalExpenses = round(
            $expSalaries + $expHousing + $expBenefits + $expFuel +
            $expMaintenance + $expViolations + $expAdvances + $expCompany, 2
        );

        // KPI shortcuts
        $totalViolationsAmt  = $expViolations;
        $totalMaintenanceAmt = $expMaintenance;
        $totalFuelAmt        = $expFuel;

        // ── Top 5 Drivers ────────────────────────────────────────────────────────
        $topHs = DB::table('hungerstation_ftr_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw('d.name, SUM(s.total_orders) as orders, SUM(s.basic_payment) as revenue')
            ->groupBy('d.id', 'd.name')
            ->orderByDesc('orders')
            ->limit(5)
            ->get();

        $topCz = DB::table('chefz_delegate_settlements as s')
            ->join('delegates as d', 'd.id', '=', 's.delegate_id')
            ->selectRaw('d.name, SUM(s.total_orders) as orders, SUM(s.company_share_amount) as profit')
            ->groupBy('d.id', 'd.name')
            ->orderByDesc('orders')
            ->limit(5)
            ->get();

        // ── Vehicle summary ──────────────────────────────────────────────────────
        $vehiclesActive          = $totalVehicles;
        $vehiclesWithViolations  = DB::table('vehicle_violations')->distinct('vehicle_id')->count('vehicle_id');
        $totalVehicleViolAmt     = round((float) DB::table('vehicle_violations')->sum('amount'), 2);
        $topViolationType        = DB::table('vehicle_violations as vv')
            ->join('warning_types as wt', 'wt.id', '=', 'vv.warning_type_id')
            ->selectRaw('wt.name, COUNT(*) as cnt')
            ->groupBy('wt.id', 'wt.name')
            ->orderByDesc('cnt')
            ->limit(1)
            ->first();

        return view('dashboard.welcome', compact(
            // counts
            'totalDelegates', 'totalVehicles',
            // orders & profit
            'hsOrders', 'czOrders', 'totalOrders',
            'hsProfit', 'czProfit', 'totalProfit',
            'activeDriverCount', 'avgProfitPerDriver',
            // KPI shortcuts
            'totalViolationsAmt', 'totalMaintenanceAmt', 'totalFuelAmt',
            // expenses
            'expSalaries', 'expHousing', 'expBenefits', 'expFuel',
            'expMaintenance', 'expViolations', 'expAdvances', 'expCompany',
            'totalExpenses',
            // rankings
            'topHs', 'topCz',
            // vehicles
            'vehiclesActive', 'vehiclesWithViolations',
            'totalVehicleViolAmt', 'topViolationType'
        ));
    }
}
