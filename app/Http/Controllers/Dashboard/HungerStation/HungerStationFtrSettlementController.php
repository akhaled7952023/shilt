<?php

namespace App\Http\Controllers\Dashboard\HungerStation;

use App\Http\Controllers\Controller;
use App\Models\HungerStationFtrDelegateDeduction;
use App\Models\HungerStationFtrImportBatch;
use App\Models\HungerStationFtrSettlement;
use App\Models\MonthlyPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HungerStationFtrSettlementController extends Controller
{
    public function index(MonthlyPeriod $period): View
    {
        $settlements = HungerStationFtrSettlement::where('monthly_period_id', $period->id)
            ->with(['delegate'])
            ->withCount('deductions')
            ->orderByDesc('net_salary')
            ->get();

        $totals = [
            'basic_payment'            => $settlements->sum('basic_payment'),
            'distance_payment'         => $settlements->sum('distance_payment'),
            'total_platform_penalties' => $settlements->sum('total_platform_penalties'),
            'stacking_deduction'       => $settlements->sum('stacking_deduction'),
            'rider_balance'            => $settlements->sum('rider_balance'),
            'company_benefits_total'   => $settlements->sum('company_benefits_total'),
            'company_deductions_total' => $settlements->sum('company_deductions_total'),
            'net_salary'               => $settlements->sum('net_salary'),
        ];

        $activeBatch = HungerStationFtrImportBatch::where('monthly_period_id', $period->id)
            ->where('status', 'completed')
            ->with('importedBy')
            ->latest()
            ->first();

        return view('dashboard.monthly.hungerstation_ftr.settlement_index',
            compact('period', 'settlements', 'totals', 'activeBatch'));
    }

    public function show(MonthlyPeriod $period, HungerStationFtrSettlement $settlement): View
    {
        abort_if($settlement->monthly_period_id !== $period->id, 404);

        $settlement->load(['delegate', 'batch.importedBy', 'deductions', 'updatedBy', 'createdBy']);

        $isReadOnly   = $settlement->is_locked || $period->isClosed();
        $validTabs    = ['imported', 'calculation', 'adjustments', 'summary', 'review', 'result'];
        $requestedTab = request()->query('tab', 'imported');

        // Legacy tab name redirect
        if ($requestedTab === 'deductions') {
            $requestedTab = 'adjustments';
        }

        $activeTab = in_array($requestedTab, $validTabs) ? $requestedTab : 'imported';

        return view('dashboard.monthly.hungerstation_ftr.settlement_show',
            compact('period', 'settlement', 'isReadOnly', 'activeTab'));
    }

    // ── Company Adjustments (Benefits + Deductions) ───────────────────────

    public function storeAdjustment(
        Request $request,
        MonthlyPeriod $period,
        HungerStationFtrSettlement $settlement
    ): RedirectResponse|JsonResponse {
        abort_if($settlement->monthly_period_id !== $period->id, 404);

        if ($settlement->is_locked || $period->isClosed()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'هذه التسوية مقفلة.'], 403);
            }
            return back()->with('error', 'هذه التسوية مقفلة ولا يمكن إضافة تسويات.');
        }

        $isBenefit      = (bool) $request->input('is_benefit', false);
        $typeLabels     = $isBenefit
            ? HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS
            : HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS;
        $validTypes     = array_keys($typeLabels);

        $data = $request->validate([
            'deduction_type' => ['required', 'in:' . implode(',', $validTypes)],
            'is_benefit'     => ['nullable', 'boolean'],
            'label'          => ['required_if:deduction_type,other,other_benefit', 'nullable', 'string', 'max:200'],
            'amount'         => ['required', 'numeric', 'gt:0'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ], [
            'deduction_type.required' => 'يرجى اختيار نوع التسوية.',
            'amount.required'         => 'يرجى إدخال المبلغ.',
            'amount.gt'               => 'يجب أن يكون المبلغ أكبر من صفر.',
            'label.required_if'       => 'يرجى إدخال وصف عند اختيار "أخرى".',
        ]);

        $label = in_array($data['deduction_type'], ['other', 'other_benefit'])
            ? ($data['label'] ?? ($isBenefit ? 'مزية أخرى' : 'خصم آخر'))
            : ($typeLabels[$data['deduction_type']] ?? $data['deduction_type']);

        HungerStationFtrDelegateDeduction::create([
            'settlement_id'     => $settlement->id,
            'monthly_period_id' => $period->id,
            'delegate_id'       => $settlement->delegate_id,
            'deduction_type'    => $data['deduction_type'],
            'is_benefit'        => $isBenefit,
            'label'             => $label,
            'amount'            => $data['amount'],
            'notes'             => $data['notes'] ?? null,
            'created_by'        => Auth::id(),
        ]);

        $settlement->recalculate();
        $settlement->updated_by = Auth::id();
        $settlement->save();

        if ($request->ajax()) {
            $settlement->load('deductions');
            return response()->json($this->buildAjaxResponse($settlement));
        }

        return redirect()
            ->route('dashboard.monthly.periods.hungerstation.ftr.settlement.show',
                [$period, $settlement, 'tab' => 'adjustments'])
            ->with('success', $isBenefit
                ? 'تم إضافة الميزة وإعادة حساب صافي الراتب.'
                : 'تم إضافة الخصم وإعادة حساب صافي الراتب.');
    }

    public function updateAdjustment(
        Request $request,
        MonthlyPeriod $period,
        HungerStationFtrSettlement $settlement,
        HungerStationFtrDelegateDeduction $deduction
    ): RedirectResponse|JsonResponse {
        abort_if($settlement->monthly_period_id !== $period->id, 404);
        abort_if($deduction->settlement_id !== $settlement->id, 404);

        if ($settlement->is_locked || $period->isClosed()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'هذه التسوية مقفلة.'], 403);
            }
            return back()->with('error', 'هذه التسوية مقفلة.');
        }

        $isBenefit  = $deduction->is_benefit;
        $typeLabels = $isBenefit
            ? HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS
            : HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS;
        $validTypes = array_keys($typeLabels);

        $data = $request->validate([
            'deduction_type' => ['required', 'in:' . implode(',', $validTypes)],
            'label'          => ['required_if:deduction_type,other,other_benefit', 'nullable', 'string', 'max:200'],
            'amount'         => ['required', 'numeric', 'gt:0'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $label = in_array($data['deduction_type'], ['other', 'other_benefit'])
            ? ($data['label'] ?? $deduction->label)
            : ($typeLabels[$data['deduction_type']] ?? $data['deduction_type']);

        $deduction->update([
            'deduction_type' => $data['deduction_type'],
            'label'          => $label,
            'amount'         => $data['amount'],
            'notes'          => $data['notes'] ?? null,
        ]);

        $settlement->recalculate();
        $settlement->updated_by = Auth::id();
        $settlement->save();

        if ($request->ajax()) {
            $settlement->load('deductions');
            return response()->json($this->buildAjaxResponse($settlement));
        }

        return redirect()
            ->route('dashboard.monthly.periods.hungerstation.ftr.settlement.show',
                [$period, $settlement, 'tab' => 'adjustments'])
            ->with('success', 'تم تعديل التسوية وإعادة الحساب.');
    }

    public function destroyAdjustment(
        MonthlyPeriod $period,
        HungerStationFtrSettlement $settlement,
        HungerStationFtrDelegateDeduction $deduction
    ): RedirectResponse|JsonResponse {
        abort_if($settlement->monthly_period_id !== $period->id, 404);
        abort_if($deduction->settlement_id !== $settlement->id, 404);

        if ($settlement->is_locked || $period->isClosed()) {
            return back()->with('error', 'هذه التسوية مقفلة ولا يمكن حذف التسويات.');
        }

        $deletedId = $deduction->id;
        $deduction->delete();

        $settlement->recalculate();
        $settlement->updated_by = Auth::id();
        $settlement->save();

        if (request()->ajax()) {
            $settlement->load('deductions');
            return response()->json([
                'deleted_id' => $deletedId,
                ...$this->buildAjaxResponse($settlement),
            ]);
        }

        return redirect()
            ->route('dashboard.monthly.periods.hungerstation.ftr.settlement.show',
                [$period, $settlement, 'tab' => 'adjustments'])
            ->with('success', 'تم حذف التسوية وإعادة الحساب.');
    }

    // ── Legacy route aliases (backward compat with old bookmark URLs) ─────

    public function storeDeduction(
        Request $request,
        MonthlyPeriod $period,
        HungerStationFtrSettlement $settlement
    ): RedirectResponse|JsonResponse {
        return $this->storeAdjustment($request, $period, $settlement);
    }

    public function destroyDeduction(
        MonthlyPeriod $period,
        HungerStationFtrSettlement $settlement,
        HungerStationFtrDelegateDeduction $deduction
    ): RedirectResponse|JsonResponse {
        return $this->destroyAdjustment($period, $settlement, $deduction);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function buildAjaxResponse(HungerStationFtrSettlement $settlement): array
    {
        return [
            'net_salary'               => $settlement->net_salary,
            'company_benefits_total'   => $settlement->company_benefits_total,
            'company_deductions_total' => $settlement->company_deductions_total,
            'adjustments'              => $settlement->deductions->map(fn($d) => [
                'id'             => $d->id,
                'is_benefit'     => (bool) $d->is_benefit,
                'deduction_type' => $d->deduction_type,
                'label'          => $d->label,
                'amount'         => $d->amount,
                'notes'          => $d->notes,
            ])->values(),
        ];
    }
}
