<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Monthly\StoreCompanyExpenseRequest;
use App\Models\CompanyExpense;
use App\Models\MonthlyPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyExpenseController extends Controller
{
    public function index(MonthlyPeriod $period): View
    {
        $this->abortIfWrongPlatform($period);

        $expenses = CompanyExpense::where('monthly_period_id', $period->id)
            ->with(['createdBy', 'updatedBy'])
            ->orderByDesc('created_at')
            ->get();

        $total = $expenses->sum('amount');

        return view('dashboard.monthly.expenses.index',
            compact('period', 'expenses', 'total'));
    }

    public function store(StoreCompanyExpenseRequest $request, MonthlyPeriod $period): RedirectResponse
    {
        $this->abortIfWrongPlatform($period);
        $this->abortIfClosed($period);

        DB::transaction(function () use ($request, $period) {
            CompanyExpense::create([
                'monthly_period_id' => $period->id,
                'category'          => $request->category,
                'amount'            => $request->amount,
                'notes'             => $request->notes,
                'created_by'        => Auth::id(),
            ]);
        });

        return back()->with('success', 'تم إضافة المصروف بنجاح.');
    }

    public function update(Request $request, MonthlyPeriod $period, CompanyExpense $expense): RedirectResponse
    {
        $this->abortIfWrongPlatform($period);
        $this->abortIfClosed($period);
        abort_if($expense->monthly_period_id !== $period->id, 404);

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'amount'   => ['required', 'numeric', 'min:0.01'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $expense) {
            $expense->update([
                'category'   => $validated['category'],
                'amount'     => $validated['amount'],
                'notes'      => $validated['notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'تم تحديث المصروف بنجاح.');
    }

    public function destroy(MonthlyPeriod $period, CompanyExpense $expense): RedirectResponse
    {
        $this->abortIfWrongPlatform($period);
        $this->abortIfClosed($period);
        abort_if($expense->monthly_period_id !== $period->id, 404);

        $expense->delete();

        return back()->with('success', 'تم حذف المصروف بنجاح.');
    }

    private function abortIfWrongPlatform(MonthlyPeriod $period): void
    {
        if ($period->platform?->code !== 'hungerstation') {
            abort(404, 'مصروفات الشركة متاحة لهنقرستيشن فقط.');
        }
    }

    private function abortIfClosed(MonthlyPeriod $period): void
    {
        if ($period->isClosed()) {
            abort(403, 'الفترة مغلقة — لا يمكن إجراء تعديلات.');
        }
    }
}
