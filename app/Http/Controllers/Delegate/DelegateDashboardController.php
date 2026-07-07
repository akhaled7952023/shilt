<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use App\Models\DelegateNotification;
use App\Models\HungerStationFtrSettlement;
use App\Models\ChefzDelegateSettlement;
use Illuminate\Support\Facades\Auth;

class DelegateDashboardController extends Controller
{
    public function index()
    {
        $delegate = Auth::guard('delegate')->user();
        $delegate->load(['platform', 'city']);

        $platformCode = $delegate->platform?->code ?? 'hungerstation';

        $vehicleAssignment = $delegate->vehicleAssignments()
            ->where('is_active', true)
            ->with('vehicle')
            ->latest('assigned_at')
            ->first();

        // Only show published/closed periods to delegates
        $pubFilter = fn($q) => $q->whereIn('status', ['published', 'closed']);

        if ($platformCode === 'hungerstation') {
            $base = HungerStationFtrSettlement::where('delegate_id', $delegate->id)
                ->whereHas('period', $pubFilter);

            $currentSettlement = (clone $base)->with('period')->latest('created_at')->first();
            $recentSettlements = (clone $base)->with('period')->latest('created_at')->limit(6)->get();
            $bestMonth         = (clone $base)->with('period:id,year,month,label,status')->orderByDesc('net_salary')->first();
        } else {
            // Chefz: each month has up to 2 payouts — aggregate to monthly totals for display

            $base = ChefzDelegateSettlement::where('delegate_id', $delegate->id)
                ->whereHas('period', $pubFilter);

            // Most recent period — combine both payouts into one monthly total
            $latestPeriodId = (clone $base)
                ->orderByDesc('monthly_period_id')
                ->limit(1)
                ->value('monthly_period_id');

            if ($latestPeriodId) {
                $latestPayouts     = (clone $base)->where('monthly_period_id', $latestPeriodId)->with('period')->orderBy('payout_number')->get();
                $currentSettlement = $this->buildChefzMonthlyTotal($latestPayouts);
            } else {
                $currentSettlement = null;
            }

            // Last 6 periods — each as a monthly total
            $recentPeriodIds = (clone $base)
                ->selectRaw('monthly_period_id')
                ->distinct()
                ->orderByDesc('monthly_period_id')
                ->limit(6)
                ->pluck('monthly_period_id');

            $recentSettlements = collect();
            foreach ($recentPeriodIds as $pid) {
                $payoutsForPeriod = (clone $base)->where('monthly_period_id', $pid)->with('period')->orderBy('payout_number')->get();
                $recentSettlements->push($this->buildChefzMonthlyTotal($payoutsForPeriod));
            }

            // Best month: the period with the highest combined net salary
            $bestPeriodId = (clone $base)
                ->selectRaw('monthly_period_id, SUM(net_salary) as total_net')
                ->groupBy('monthly_period_id')
                ->orderByDesc('total_net')
                ->limit(1)
                ->value('monthly_period_id');

            if ($bestPeriodId) {
                $bestPayouts = (clone $base)->where('monthly_period_id', $bestPeriodId)->with('period:id,year,month,label,status')->orderBy('payout_number')->get();
                $bestMonth   = $this->buildChefzMonthlyTotal($bestPayouts);
            } else {
                $bestMonth = null;
            }
        }

        $unreadCount = DelegateNotification::where('delegate_id', $delegate->id)
            ->whereNull('read_at')
            ->count();

        return view('portal.dashboard.index', compact(
            'delegate', 'vehicleAssignment',
            'currentSettlement', 'recentSettlements',
            'platformCode', 'bestMonth', 'unreadCount'
        ));
    }

    /**
     * Aggregate individual Chefz payout records into a single virtual monthly total.
     */
    private function buildChefzMonthlyTotal($payouts): ChefzDelegateSettlement
    {
        $total = new ChefzDelegateSettlement();
        $total->delegate_id            = $payouts->first()->delegate_id;
        $total->monthly_period_id      = $payouts->first()->monthly_period_id;
        $total->payout_number          = 0;
        $total->total_orders           = $payouts->sum('total_orders');
        $total->gross_delivery_fees    = $payouts->sum('gross_delivery_fees');
        $total->platform_deductions    = $payouts->sum('platform_deductions');
        $total->platform_compensations = $payouts->sum('platform_compensations');
        $total->bonus_total            = $payouts->sum('bonus_total');
        $total->positive_bonus         = $payouts->sum('positive_bonus');
        $total->chefz_tax_rate         = $payouts->first()->chefz_tax_rate;
        $total->chefz_tax_amount       = $payouts->sum('chefz_tax_amount');
        $total->company_share_rate     = $payouts->first()->company_share_rate;
        $total->company_share_amount   = $payouts->sum('company_share_amount');
        $total->commission_total       = $payouts->sum('commission_total');
        $total->deductions_total       = $payouts->sum('deductions_total');
        $total->net_salary             = $payouts->sum('net_salary');
        $total->setRelation('period',  $payouts->first()->period);
        return $total;
    }
}
