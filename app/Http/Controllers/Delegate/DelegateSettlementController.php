<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use App\Models\HungerStationFtrSettlement;
use App\Models\ChefzDelegateSettlement;
use App\Models\MonthlyPeriod;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class DelegateSettlementController extends Controller
{
    public function index(Request $request)
    {
        $delegate     = Auth::guard('delegate')->user();
        $delegate->load('platform');
        $platformCode = $delegate->platform?->code ?? 'hungerstation';
        $year         = $request->integer('year') ?: null;

        if ($platformCode === 'the-chefz') {
            return $this->chefzIndex($delegate, $year);
        }

        $base = HungerStationFtrSettlement::where('delegate_id', $delegate->id)
            ->whereHas('period', fn($q) => $q->whereIn('status', ['published', 'closed']));

        $availableYears = (clone $base)
            ->join('monthly_periods', 'monthly_periods.id', '=', 'monthly_period_id')
            ->orderByDesc('monthly_periods.year')
            ->distinct()
            ->pluck('monthly_periods.year')
            ->filter()
            ->values();

        $settlements = (clone $base)
            ->with('period')
            ->when($year, fn($q) => $q->whereHas('period', fn($pq) => $pq->where('year', $year)))
            ->latest('created_at')
            ->get();

        return view('portal.settlements.index', compact(
            'delegate', 'settlements', 'platformCode', 'availableYears', 'year'
        ));
    }

    public function show(MonthlyPeriod $period, Request $request)
    {
        $delegate = Auth::guard('delegate')->user();
        $delegate->load(['platform', 'city']);
        $platformCode = $delegate->platform?->code ?? 'hungerstation';

        if (! $period->status->isVisibleToDelegate()) {
            abort(403, __('portal.val_report_not_available'));
        }

        if ($platformCode === 'the-chefz') {
            return $this->chefzShow($delegate, $period, $request->query('payout', 'total'));
        }

        $settlement = HungerStationFtrSettlement::where('delegate_id', $delegate->id)
            ->where('monthly_period_id', $period->id)
            ->with(['deductions'])
            ->firstOrFail();

        $companyName = (App::isLocale('en') ? SystemSetting::get('company_name_en') : null)
            ?? SystemSetting::get('company_name_ar')
            ?? 'شيلت للخدمات اللوجستية';

        return view('portal.settlements.show', compact(
            'delegate', 'period', 'settlement', 'platformCode', 'companyName'
        ));
    }

    public function printView(MonthlyPeriod $period, Request $request)
    {
        $delegate = Auth::guard('delegate')->user();
        $delegate->load(['platform', 'city']);
        $platformCode = $delegate->platform?->code ?? 'hungerstation';

        if (! $period->status->isVisibleToDelegate()) {
            abort(403);
        }

        if ($platformCode === 'the-chefz') {
            return $this->chefzPrint($delegate, $period, $request->query('payout', 'total'));
        }

        $settlement = HungerStationFtrSettlement::where('delegate_id', $delegate->id)
            ->where('monthly_period_id', $period->id)
            ->with(['deductions'])
            ->firstOrFail();

        $companyNameEn  = SystemSetting::get('company_name_en') ?? '';
        $companyName    = (App::isLocale('en') && $companyNameEn)
            ? $companyNameEn
            : (SystemSetting::get('company_name_ar') ?? 'شيلت للخدمات اللوجستية');
        $companyLogo    = SystemSetting::get('company_logo_path') ?? '';
        $companyCr      = SystemSetting::get('company_cr') ?? '';
        $companyAddress = SystemSetting::get('company_address') ?? '';
        $companyPhone   = SystemSetting::get('company_phone') ?? '';

        return view('portal.settlements.print', compact(
            'delegate', 'period', 'settlement', 'platformCode',
            'companyName', 'companyNameEn', 'companyLogo', 'companyCr', 'companyAddress', 'companyPhone'
        ));
    }

    // ── Chefz-specific handlers ──────────────────────────────────────────────────

    private function chefzIndex($delegate, ?int $year)
    {
        $base = ChefzDelegateSettlement::where('delegate_id', $delegate->id)
            ->whereHas('period', fn($q) => $q->whereIn('status', ['published', 'closed']));

        $availableYears = (clone $base)
            ->join('monthly_periods', 'monthly_periods.id', '=', 'monthly_period_id')
            ->orderByDesc('monthly_periods.year')
            ->distinct()
            ->pluck('monthly_periods.year')
            ->filter()
            ->values();

        $rawSettlements = (clone $base)
            ->with('period')
            ->when($year, fn($q) => $q->whereHas('period', fn($pq) => $pq->where('year', $year)))
            ->orderByDesc('monthly_period_id')
            ->orderBy('payout_number')
            ->get();

        // Group by period: build virtual "monthly total" entries
        $grouped = $rawSettlements->groupBy('monthly_period_id');
        $platformCode = 'the-chefz';

        return view('portal.settlements.chefz_index', compact(
            'delegate', 'grouped', 'availableYears', 'year', 'platformCode'
        ));
    }

    private function chefzShow($delegate, MonthlyPeriod $period, string $payoutParam)
    {
        $payouts = ChefzDelegateSettlement::where('delegate_id', $delegate->id)
            ->where('monthly_period_id', $period->id)
            ->orderBy('payout_number')
            ->get();

        abort_if($payouts->isEmpty(), 404);

        $payout1     = $payouts->firstWhere('payout_number', 1);
        $payout2     = $payouts->firstWhere('payout_number', 2);
        $platformCode = 'the-chefz';
        $companyName  = (App::isLocale('en') ? SystemSetting::get('company_name_en') : null)
            ?? SystemSetting::get('company_name_ar')
            ?? 'شيلت للخدمات اللوجستية';

        if ($payoutParam === '1' && $payout1) {
            $settlement  = $payout1;
            $viewMode    = 'payout1';
        } elseif ($payoutParam === '2' && $payout2) {
            $settlement  = $payout2;
            $viewMode    = 'payout2';
        } else {
            // Monthly total — build a virtual settlement from both payouts
            $settlement  = $this->buildMonthlyTotal($payouts);
            $viewMode    = 'total';
        }

        return view('portal.settlements.chefz_show', compact(
            'delegate', 'period', 'settlement', 'platformCode', 'companyName',
            'payout1', 'payout2', 'viewMode'
        ));
    }

    private function chefzPrint($delegate, MonthlyPeriod $period, string $payoutParam)
    {
        $payouts = ChefzDelegateSettlement::where('delegate_id', $delegate->id)
            ->where('monthly_period_id', $period->id)
            ->orderBy('payout_number')
            ->get();

        abort_if($payouts->isEmpty(), 404);

        $payout1     = $payouts->firstWhere('payout_number', 1);
        $payout2     = $payouts->firstWhere('payout_number', 2);
        $platformCode = 'the-chefz';
        $companyNameEn  = SystemSetting::get('company_name_en') ?? '';
        $companyName    = (App::isLocale('en') && $companyNameEn)
            ? $companyNameEn
            : (SystemSetting::get('company_name_ar') ?? 'شيلت للخدمات اللوجستية');
        $companyLogo    = SystemSetting::get('company_logo_path') ?? '';
        $companyCr      = SystemSetting::get('company_cr') ?? '';
        $companyAddress = SystemSetting::get('company_address') ?? '';
        $companyPhone   = SystemSetting::get('company_phone') ?? '';

        if ($payoutParam === '1' && $payout1) {
            $settlement = $payout1;
            $viewMode   = 'payout1';
        } elseif ($payoutParam === '2' && $payout2) {
            $settlement = $payout2;
            $viewMode   = 'payout2';
        } else {
            $settlement = $this->buildMonthlyTotal($payouts);
            $viewMode   = 'total';
        }

        return view('portal.settlements.chefz_print', compact(
            'delegate', 'period', 'settlement', 'platformCode',
            'companyName', 'companyNameEn', 'companyLogo', 'companyCr', 'companyAddress', 'companyPhone',
            'payout1', 'payout2', 'viewMode'
        ));
    }

    /**
     * Build a virtual "monthly total" object by summing P1 + P2 settlement fields.
     */
    private function buildMonthlyTotal($payouts): ChefzDelegateSettlement
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
        $total->is_locked              = false;
        $total->setRelation('delegate', $payouts->first()->delegate);
        $total->setRelation('period',   $payouts->first()->period);

        return $total;
    }
}
