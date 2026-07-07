<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Monthly\StoreMonthlyPeriodRequest;
use App\Models\MonthlyPeriod;
use App\Models\Platform;
use App\Services\Dashboard\Chefz\ChefzImportService;
use App\Services\Dashboard\HungerStation\HungerStationFtrImportService;
use App\Services\Dashboard\Monthly\IMonthlyPeriodService;
use Illuminate\Support\Facades\DB;

class MonthlyPeriodController extends Controller
{
    public function __construct(
        protected IMonthlyPeriodService $service,
        protected HungerStationFtrImportService $hsImportService,
        protected ChefzImportService $chefzImportService,
    ) {}

    public function index()
    {
        $periods = $this->service->getAll();

        return view('dashboard.monthly.periods.index', compact('periods'));
    }

    public function create()
    {
        $platforms = Platform::where('is_active', true)->orderBy('name')->get();

        $years  = range(2024, 2030);
        $months = $this->arabicMonths();

        return view('dashboard.monthly.periods.create', compact('platforms', 'years', 'months'));
    }

    public function store(StoreMonthlyPeriodRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $this->service->create($data);

        return redirect()
            ->route('dashboard.monthly.periods.index')
            ->with('success', 'تم إنشاء الفترة الشهرية بنجاح.');
    }

    public function show(MonthlyPeriod $period)
    {
        $period->load('platform', 'closedBy');

        return view('dashboard.monthly.periods.show', compact('period'));
    }

    public function destroy(MonthlyPeriod $period)
    {
        if ($period->isClosed()) {
            return back()->with('error', 'لا يمكن حذف فترة مغلقة.');
        }

        if (! $period->isOpen()) {
            return back()->with('error', 'لا يمكن حذف الفترة في حالتها الحالية.');
        }

        DB::transaction(function () use ($period) {
            $this->hsImportService->deletePeriodFtrData($period);
            $this->chefzImportService->deletePeriodChefzData($period);
            $period->delete();
        });

        return redirect()
            ->route('dashboard.monthly.periods.index')
            ->with('success', 'تم حذف الفترة الشهرية وجميع البيانات المرتبطة بها.');
    }

    public function close(MonthlyPeriod $period)
    {
        try {
            $this->service->close($period->id, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('dashboard.monthly.periods.index')
            ->with('success', "تم إغلاق فترة {$period->getDisplayLabel()} بنجاح.");
    }

    public function reopen(MonthlyPeriod $period)
    {
        try {
            $this->service->reopen($period->id);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('dashboard.monthly.periods.show', $period)
            ->with('success', "تم إعادة فتح فترة {$period->getDisplayLabel()} بنجاح.");
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function arabicMonths(): array
    {
        return [
            1  => 'يناير',
            2  => 'فبراير',
            3  => 'مارس',
            4  => 'أبريل',
            5  => 'مايو',
            6  => 'يونيو',
            7  => 'يوليو',
            8  => 'أغسطس',
            9  => 'سبتمبر',
            10 => 'أكتوبر',
            11 => 'نوفمبر',
            12 => 'ديسمبر',
        ];
    }
}
