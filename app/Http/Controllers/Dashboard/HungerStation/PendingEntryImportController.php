<?php

namespace App\Http\Controllers\Dashboard\HungerStation;

use App\Http\Controllers\Controller;
use App\Models\MonthlyPeriod;
use App\Services\Support\PendingEntryImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PendingEntryImportController extends Controller
{
    public function __construct(
        private readonly PendingEntryImportService $importService,
    ) {}

    public function apply(MonthlyPeriod $period): RedirectResponse
    {
        $redirect = redirect()->route(
            'dashboard.monthly.periods.hungerstation.ftr.settlement.index',
            $period
        );

        try {
            $result = $this->importService->apply($period, Auth::user());
        } catch (\Throwable $e) {
            flash()->error('فشل الاستيراد وتم التراجع عن جميع التغييرات. ' . $e->getMessage());
            return $redirect;
        }

        if ($result['imported'] === 0 && $result['skipped'] === 0) {
            flash()->info('لا توجد قيود معلقة لهذه الفترة.');
            return $redirect;
        }

        $msg = "تم استيراد {$result['imported']} قيد بنجاح وإعادة حساب التسويات.";
        if ($result['skipped'] > 0) {
            $msg .= " تم تخطي {$result['skipped']}: " . implode(' | ', $result['reasons']);
        }

        flash()->success($msg);
        return $redirect;
    }
}
