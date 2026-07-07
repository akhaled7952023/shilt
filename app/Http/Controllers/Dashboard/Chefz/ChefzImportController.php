<?php

namespace App\Http\Controllers\Dashboard\Chefz;

use App\DTOs\ChefzImportPreviewDTO;
use App\Exceptions\Import\ImportHeaderException;
use App\Http\Controllers\Controller;
use App\Models\ChefzImportBatch;
use App\Models\MonthlyPeriod;
use App\Models\SystemSetting;
use App\Services\Dashboard\Chefz\ChefzImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChefzImportController extends Controller
{
    private const SESSION_DTO = 'chefz_import_dto';

    public function __construct(
        private readonly ChefzImportService $importService
    ) {}

    public function showUploadForm(MonthlyPeriod $period): View|RedirectResponse
    {
        $this->abortIfWrongPlatform($period);

        if ($period->isClosed()) {
            return redirect()
                ->route('dashboard.monthly.periods.show', $period)
                ->with('error', 'لا يمكن الاستيراد — هذه الفترة مغلقة.');
        }

        $batches = ChefzImportBatch::where('monthly_period_id', $period->id)
            ->where('status', 'completed')
            ->with('importedBy')
            ->orderByDesc('imported_at')
            ->get();

        $payout1Batch = $batches->firstWhere('payout_number', 1);
        $payout2Batch = $batches->firstWhere('payout_number', 2);
        $isMonthComplete = $payout1Batch && $payout2Batch;

        return view('dashboard.monthly.chefz.import', compact(
            'period', 'batches', 'payout1Batch', 'payout2Batch', 'isMonthComplete'
        ));
    }

    public function upload(Request $request, MonthlyPeriod $period): RedirectResponse
    {
        $this->abortIfWrongPlatform($period);

        if ($period->isClosed()) {
            abort(403, 'الفترة مغلقة — لا يمكن الاستيراد.');
        }

        $maxMb = (int) (SystemSetting::get('import_max_file_mb') ?? 20);
        $maxKb = $maxMb * 1024;

        $request->validate([
            'file'          => ['required', 'file', 'mimes:xlsx', 'max:' . $maxKb],
            'payout_number' => ['required', 'integer', 'in:1,2'],
        ], [
            'file.required'          => 'يرجى اختيار ملف للرفع.',
            'file.mimes'             => 'يجب أن يكون الملف بصيغة Excel (.xlsx).',
            'file.max'               => "حجم الملف يتجاوز الحد المسموح ({$maxMb} ميغابايت).",
            'payout_number.required' => 'يرجى تحديد رقم الدفعة.',
            'payout_number.in'       => 'رقم الدفعة يجب أن يكون 1 أو 2.',
        ]);

        $payoutNumber    = (int) $request->input('payout_number');
        $uuid            = (string) Str::uuid();
        $tempStoragePath = 'imports/temp/' . $uuid . '.xlsx';
        Storage::makeDirectory('imports/temp');
        $request->file('file')->storeAs('imports/temp', $uuid . '.xlsx');

        $localPath        = Storage::path($tempStoragePath);
        $originalFilename = $request->file('file')->getClientOriginalName();
        $fileSizeBytes    = $request->file('file')->getSize();

        try {
            $dto = $this->importService->parseAndValidate(
                localFilePath:    $localPath,
                monthlyPeriodId:  $period->id,
                payoutNumber:     $payoutNumber,
                tempStoragePath:  $tempStoragePath,
                originalFilename: $originalFilename,
                fileSizeBytes:    $fileSizeBytes,
            );

            if ($dto->newRows === 0 && $dto->totalRows > 0) {
                Storage::delete($tempStoragePath);
                return back()->with('error', 'لا توجد طلبات صالحة في هذا الملف.');
            }

            session([self::SESSION_DTO => $dto]);

            return redirect()->route('dashboard.monthly.periods.chefz.import.preview', $period);
        } catch (ImportHeaderException $e) {
            Storage::delete($tempStoragePath);
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Storage::delete($tempStoragePath);
            Log::error('Chefz import parse error', ['error' => $e->getMessage(), 'period' => $period->id]);
            return back()->with('error', 'حدث خطأ أثناء معالجة الملف. يرجى التحقق من صحة الملف والمحاولة مرة أخرى.');
        }
    }

    public function preview(MonthlyPeriod $period): View|RedirectResponse
    {
        $dto = session(self::SESSION_DTO);

        if (! $dto instanceof ChefzImportPreviewDTO) {
            return redirect()
                ->route('dashboard.monthly.periods.chefz.import', $period)
                ->with('error', 'انتهت جلسة المعاينة. يرجى رفع الملف من جديد.');
        }

        return view('dashboard.monthly.chefz.preview', compact('period', 'dto'));
    }

    public function confirm(MonthlyPeriod $period): RedirectResponse
    {
        $dto = session(self::SESSION_DTO);

        if (! $dto instanceof ChefzImportPreviewDTO) {
            return redirect()
                ->route('dashboard.monthly.periods.chefz.import', $period)
                ->with('error', 'انتهت جلسة المعاينة. يرجى رفع الملف من جديد.');
        }

        if ($period->isClosed()) {
            session()->forget(self::SESSION_DTO);
            abort(403, 'الفترة مغلقة — لا يمكن الاستيراد.');
        }

        if (! $dto->isConfirmable) {
            return redirect()
                ->route('dashboard.monthly.periods.chefz.import.preview', $period)
                ->with('error', 'لا يمكن إتمام الاستيراد — يوجد أخطاء في البيانات.');
        }

        try {
            $batch = $this->importService->confirm($dto, $period, Auth::id());
            session()->forget(self::SESSION_DTO);

            $payoutLabel = $batch->getPayoutLabel();
            return redirect()
                ->route('dashboard.monthly.periods.chefz.settlement.index', $period)
                ->with('success', "تم استيراد {$payoutLabel} بنجاح — {$batch->total_rows} طلب، {$batch->unique_delegates} مندوب.");
        } catch (\Throwable $e) {
            $uuid = Str::uuid();
            Log::error('Chefz import confirm failed', [
                'uuid'   => $uuid,
                'period' => $period->id,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('dashboard.monthly.periods.chefz.import.preview', $period)
                ->with('error', "فشل الاستيراد — لم يُحفَظ أي شيء. رقم المرجع: {$uuid}");
        }
    }

    public function cancel(MonthlyPeriod $period): RedirectResponse
    {
        $dto = session(self::SESSION_DTO);

        if ($dto instanceof ChefzImportPreviewDTO) {
            Storage::delete($dto->tempStoragePath);
        }

        session()->forget(self::SESSION_DTO);

        return redirect()
            ->route('dashboard.monthly.periods.show', $period)
            ->with('info', 'تم إلغاء الاستيراد.');
    }

    public function history(MonthlyPeriod $period): View
    {
        $batches = ChefzImportBatch::where('monthly_period_id', $period->id)
            ->with('importedBy')
            ->orderByDesc('imported_at')
            ->get();

        return view('dashboard.monthly.chefz.history', compact('period', 'batches'));
    }

    private function abortIfWrongPlatform(MonthlyPeriod $period): void
    {
        if ($period->platform?->code !== 'the-chefz') {
            abort(404, 'هذه الفترة لا تنتمي لمنصة شيفز.');
        }
    }
}
