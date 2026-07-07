<?php

namespace App\Services\Dashboard\Chefz;

use App\DTOs\ChefzImportPreviewDTO;
use App\Exceptions\Import\ImportHeaderException;
use App\Models\ChefzDelegateSettlement;
use App\Models\ChefzImportBatch;
use App\Models\Delegate;
use App\Models\MonthlyPeriod;
use App\Services\Import\SpreadsheetParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChefzImportService
{
    // Columns that must always be present
    private const REQUIRED_HEADERS = ['chef order id', 'driver id', 'driver name', 'date'];

    // Per spec, the canonical column is "Driver Delivery Fees With VAT".
    // Some older Payout 1 files use the shorter "Driver Delivery Fees".
    // Both map to the same gross_delivery_fees field — accept either.
    private const DELIVERY_FEE_HEADERS = ['driver delivery fees with vat', 'driver delivery fees'];

    // Headers unique to HungerStation — used for cross-platform detection
    private const HS_SIGNATURE_HEADERS = ['order id'];

    // ── Public API ───────────────────────────────────────────────────────────────

    /**
     * Parse and validate a Chefz xlsx file for a specific payout (1 or 2).
     * Does NOT write to the DB — returns a DTO stored in session until confirm.
     *
     * Replace semantics: importing Payout 1 again replaces all existing Payout 1 data.
     * In-file duplicate order IDs: first occurrence kept, rest counted as inFileDuplicates.
     */
    public function parseAndValidate(
        string $localFilePath,
        int    $monthlyPeriodId,
        int    $payoutNumber,
        string $tempStoragePath,
        string $originalFilename,
        int    $fileSizeBytes,
    ): ChefzImportPreviewDTO {
        $spreadsheet = IOFactory::load($localFilePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $colMap      = SpreadsheetParser::buildColumnMap($sheet);

        $this->detectCrossPlatformUpload($colMap);

        foreach (self::REQUIRED_HEADERS as $required) {
            if (! isset($colMap[$required])) {
                throw new ImportHeaderException($this->displayHeaderName($required));
            }
        }

        // Resolve which delivery fee column is present (one of the two variants is required)
        $deliveryFeeCol = null;
        foreach (self::DELIVERY_FEE_HEADERS as $candidate) {
            if (isset($colMap[$candidate])) {
                $deliveryFeeCol = $candidate;
                break;
            }
        }
        if ($deliveryFeeCol === null) {
            throw new ImportHeaderException('Driver Delivery Fees');
        }

        // Check whether this payout already has a completed batch (replace scenario)
        $isReplace = ChefzImportBatch::where('monthly_period_id', $monthlyPeriodId)
            ->where('payout_number', $payoutNumber)
            ->where('status', 'completed')
            ->exists();

        $parsedRows       = [];
        $warnings         = [];
        $errors           = [];
        $seenInFile       = [];
        $inFileDuplicates = 0;
        $highestRow       = $sheet->getHighestDataRow();

        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            $row = $this->readRow($sheet, $colMap, $rowIndex, $deliveryFeeCol);
            if ($row === null) {
                continue;
            }

            if (empty($row['raw_driver_id'])) {
                $warnings[] = "الصف {$rowIndex}: Driver ID فارغ — تم تخطي الصف";
                continue;
            }

            if (empty($row['order_id_platform'])) {
                $warnings[] = "الصف {$rowIndex}: Chef Order ID فارغ — تم تخطي الصف";
                continue;
            }

            // In-file duplicate: scoped per-driver so legitimate order handoffs
            // (same order_id reassigned to a different driver on breakdown/accident)
            // are preserved. True duplicates for the same driver are still dropped.
            $dedupKey = $row['raw_driver_id'] . '_' . $row['order_id_platform'];
            if (isset($seenInFile[$dedupKey])) {
                $inFileDuplicates++;
                continue;
            }
            $seenInFile[$dedupKey] = true;

            if ($row['delivery_fee'] <= 0) {
                $warnings[] = "الصف {$rowIndex}: رسوم التوصيل = 0 (Driver ID: {$row['raw_driver_id']})";
            }

            $parsedRows[] = $row;
        }

        $grouped     = $this->groupByDelegate($parsedRows);
        $driverIds   = array_keys($grouped);
        $existingMap = $driverIds
            ? Delegate::withTrashed()->whereIn('national_id', $driverIds)->pluck('id', 'national_id')->all()
            : [];

        $knownCount         = 0;
        $willCreateCount    = 0;
        $perDelegateSummary = [];

        foreach ($grouped as $driverId => $data) {
            $status = isset($existingMap[$driverId]) ? 'known' : 'will_create';
            $knownCount      += ($status === 'known')       ? 1 : 0;
            $willCreateCount += ($status === 'will_create') ? 1 : 0;

            $perDelegateSummary[] = [
                'driver_id'      => $driverId,
                'name'           => $data['name'],
                'orders'         => $data['orders'],
                'gross_fees'     => round($data['gross_fees'], 2),
                'deductions'     => round($data['deductions'], 2),
                'compensations'  => round($data['compensations'], 2),
                'bonus_total'    => round($data['bonus_total'], 2),
                'positive_bonus' => round($data['positive_bonus'], 2),
                'status'         => $status,
                'delegate_id'    => $existingMap[$driverId] ?? null,
            ];
        }

        $reconciliation = [
            'total_rows'    => count($parsedRows),
            'gross_fees'    => round(array_sum(array_column($parsedRows, 'delivery_fee')), 2),
            'deductions'    => round(array_sum(array_column($parsedRows, 'deduction_amount')), 2),
            'compensations' => round(array_sum(array_column($parsedRows, 'compensation')), 2),
            'bonus_total'   => round(array_sum(array_column($parsedRows, 'bonus_amount')), 2),
        ];

        $isConfirmable = empty($errors) && count($parsedRows) > 0;

        return new ChefzImportPreviewDTO(
            payoutNumber:       $payoutNumber,
            isReplace:          $isReplace,
            totalRows:          count($parsedRows) + $inFileDuplicates,
            newRows:            count($parsedRows),
            inFileDuplicates:   $inFileDuplicates,
            uniqueDelegates:    count($grouped),
            knownCount:         $knownCount,
            willCreateCount:    $willCreateCount,
            parsedRows:         $parsedRows,
            perDelegateSummary: $perDelegateSummary,
            reconciliation:     $reconciliation,
            warnings:           $warnings,
            errors:             $errors,
            isConfirmable:      $isConfirmable,
            tempStoragePath:    $tempStoragePath,
            originalFilename:   $originalFilename,
            fileSizeBytes:      $fileSizeBytes,
        );
    }

    /**
     * Confirm import — replace all existing data for this payout, then insert fresh.
     * After inserting orders, recalculates each affected delegate's settlement for this payout.
     */
    public function confirm(
        ChefzImportPreviewDTO $dto,
        MonthlyPeriod $period,
        int $userId
    ): ChefzImportBatch {
        $startTime   = microtime(true);
        $payoutNumber = $dto->payoutNumber;

        return DB::transaction(function () use ($dto, $period, $userId, $startTime, $payoutNumber) {
            // Delete existing batch(es) for this payout (replace semantics)
            $this->deletePayoutData($period->id, $payoutNumber);

            $permanentPath = $this->moveToPermanent($dto->tempStoragePath, $period, $dto->originalFilename);

            $batch = ChefzImportBatch::create([
                'monthly_period_id' => $period->id,
                'payout_number'     => $payoutNumber,
                'original_filename' => $dto->originalFilename,
                'file_path'         => $permanentPath,
                'file_size_bytes'   => $dto->fileSizeBytes,
                'status'            => 'processing',
                'version_number'    => 1,
                'imported_by'       => $userId,
                'imported_at'       => now(),
                'warning_count'     => count($dto->warnings),
                'error_count'       => count($dto->errors),
            ]);

            $newDelegatesCount = 0;
            $delegateIdMap     = $this->resolveDelegates(
                $dto->perDelegateSummary,
                $period,
                $userId,
                $newDelegatesCount
            );

            // Bulk-insert all orders for this payout
            $now = now()->toDateTimeString();
            foreach (array_chunk($dto->parsedRows, 100) as $chunk) {
                $rows = [];
                foreach ($chunk as $row) {
                    $rows[] = [
                        'import_batch_id'   => $batch->id,
                        'monthly_period_id' => $period->id,
                        'delegate_id'       => $delegateIdMap[$row['raw_driver_id']] ?? null,
                        'payout_number'     => $payoutNumber,
                        'raw_driver_id'     => $row['raw_driver_id'],
                        'raw_driver_name'   => $row['raw_driver_name'],
                        'order_date'        => $row['order_date'],
                        'order_id_platform' => $row['order_id_platform'],
                        'delivery_fee'      => $row['delivery_fee'],
                        'deduction_amount'  => $row['deduction_amount'],
                        'deduction_note'    => $row['deduction_note'],
                        'compensation'      => $row['compensation'],
                        'compensation_note' => $row['compensation_note'],
                        'bonus_amount'      => $row['bonus_amount'],
                        'bonus_note'        => $row['bonus_note'],
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
                DB::table('chefz_orders')->insert($rows);
            }

            // Recalculate each affected delegate's settlement for this payout
            $this->recalculateSettlements($period, $payoutNumber, $delegateIdMap, $userId);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $batch->update([
                'status'                => 'completed',
                'total_rows'            => $dto->newRows,
                'skipped_duplicates'    => 0,
                'unique_delegates'      => $dto->uniqueDelegates,
                'new_delegates_created' => $newDelegatesCount,
                'import_duration_ms'    => $durationMs,
            ]);

            return $batch;
        });
    }

    /**
     * Delete all Chefz data for a specific payout within a period.
     */
    public function deletePayoutData(int $periodId, int $payoutNumber): void
    {
        // Find batches for this payout
        $batchIds = ChefzImportBatch::where('monthly_period_id', $periodId)
            ->where('payout_number', $payoutNumber)
            ->pluck('id')
            ->toArray();

        // Delete settlement deductions for this payout
        $settlementIds = ChefzDelegateSettlement::where('monthly_period_id', $periodId)
            ->where('payout_number', $payoutNumber)
            ->pluck('id')
            ->toArray();

        if ($settlementIds) {
            DB::table('chefz_delegate_deductions')
                ->whereIn('settlement_id', $settlementIds)
                ->delete();

            DB::table('chefz_delegate_settlements')
                ->whereIn('id', $settlementIds)
                ->delete();
        }

        // Delete orders for this payout
        DB::table('chefz_orders')
            ->where('monthly_period_id', $periodId)
            ->where('payout_number', $payoutNumber)
            ->delete();

        // Delete batch files and records
        if ($batchIds) {
            foreach (ChefzImportBatch::whereIn('id', $batchIds)->get() as $batch) {
                if ($batch->file_path) {
                    Storage::delete($batch->file_path);
                }
            }
            DB::table('chefz_import_batches')->whereIn('id', $batchIds)->delete();
        }
    }

    /**
     * Cascade-delete ALL Chefz data for a period (all payouts).
     * Called by MonthlyPeriodController::destroy() before deleting the period.
     */
    public function deletePeriodChefzData(MonthlyPeriod $period): void
    {
        $batchIds = ChefzImportBatch::where('monthly_period_id', $period->id)
            ->pluck('id')
            ->toArray();

        $settlementIds = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
            ->pluck('id')
            ->toArray();

        if ($settlementIds) {
            DB::table('chefz_delegate_deductions')
                ->whereIn('settlement_id', $settlementIds)
                ->delete();

            DB::table('chefz_delegate_settlements')
                ->whereIn('id', $settlementIds)
                ->delete();
        }

        DB::table('chefz_orders')
            ->where('monthly_period_id', $period->id)
            ->delete();

        foreach (ChefzImportBatch::whereIn('id', $batchIds)->get() as $batch) {
            if ($batch->file_path) {
                Storage::delete($batch->file_path);
            }
        }

        DB::table('chefz_import_batches')
            ->where('monthly_period_id', $period->id)
            ->delete();
    }

    // ── Private helpers ──────────────────────────────────────────────────────────

    /**
     * Aggregate orders for this payout and upsert each delegate's settlement.
     */
    private function recalculateSettlements(
        MonthlyPeriod $period,
        int $payoutNumber,
        array $delegateIdMap,
        int $userId
    ): void {
        foreach ($delegateIdMap as $driverId => $delegateId) {
            if (! $delegateId) {
                continue;
            }

            $agg = DB::table('chefz_orders')
                ->where('monthly_period_id', $period->id)
                ->where('delegate_id', $delegateId)
                ->where('payout_number', $payoutNumber)
                ->selectRaw('
                    COUNT(*) as total_orders,
                    COALESCE(SUM(delivery_fee), 0)               as gross_delivery_fees,
                    COALESCE(SUM(deduction_amount), 0)           as platform_deductions,
                    COALESCE(SUM(compensation), 0)               as platform_compensations,
                    COALESCE(SUM(bonus_amount), 0)               as bonus_total,
                    COALESCE(SUM(GREATEST(bonus_amount, 0)), 0)  as positive_bonus
                ')
                ->first();

            $existing = ChefzDelegateSettlement::where('monthly_period_id', $period->id)
                ->where('delegate_id', $delegateId)
                ->where('payout_number', $payoutNumber)
                ->first();

            if ($existing) {
                $existing->update([
                    'total_orders'           => $agg->total_orders,
                    'gross_delivery_fees'    => round($agg->gross_delivery_fees, 2),
                    'platform_deductions'    => round($agg->platform_deductions, 2),
                    'platform_compensations' => round($agg->platform_compensations, 2),
                    'bonus_total'            => round($agg->bonus_total, 2),
                    'positive_bonus'         => round($agg->positive_bonus, 2),
                ]);
                $existing->recalculate();
            } else {
                $settlement = ChefzDelegateSettlement::create([
                    'monthly_period_id'      => $period->id,
                    'delegate_id'            => $delegateId,
                    'payout_number'          => $payoutNumber,
                    'total_orders'           => $agg->total_orders,
                    'gross_delivery_fees'    => round($agg->gross_delivery_fees, 2),
                    'platform_deductions'    => round($agg->platform_deductions, 2),
                    'platform_compensations' => round($agg->platform_compensations, 2),
                    'bonus_total'            => round($agg->bonus_total, 2),
                    'positive_bonus'         => round($agg->positive_bonus, 2),
                    'commission_total'       => 0,
                    'deductions_total'       => 0,
                    'net_salary'             => 0,
                    'is_locked'              => false,
                    'created_by'             => $userId,
                ]);
                $settlement->recalculate();
            }
        }
    }

    private function detectCrossPlatformUpload(array $colMap): void
    {
        foreach (self::HS_SIGNATURE_HEADERS as $hsHeader) {
            if (isset($colMap[$hsHeader]) && ! isset($colMap['chef order id'])) {
                throw new ImportHeaderException(
                    'ملف هنقرستيشن في قسم شيفز — يُرجى رفع هذا الملف في قسم "HungerStation" وليس هنا'
                );
            }
        }
    }

    private function readRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $colMap,
        int $rowIndex,
        string $deliveryFeeCol = 'driver delivery fees'
    ): ?array {
        $driverId = SpreadsheetParser::getString($sheet, $colMap, 'driver id', $rowIndex);
        $orderId  = SpreadsheetParser::getString($sheet, $colMap, 'chef order id', $rowIndex);
        $name     = SpreadsheetParser::getString($sheet, $colMap, 'driver name', $rowIndex);

        if ($driverId === '' && $orderId === '' && $name === '') {
            return null;
        }

        $dateCol   = $colMap['date'] ?? null;
        $orderDate = $dateCol ? SpreadsheetParser::parseDate($sheet, $dateCol, $rowIndex) : null;

        return [
            'raw_driver_id'     => SpreadsheetParser::normalizeId($driverId),
            'raw_driver_name'   => $name,
            'order_date'        => $orderDate,
            'order_id_platform' => SpreadsheetParser::normalizeId($orderId),
            'delivery_fee'      => SpreadsheetParser::getDecimal($sheet, $colMap, $deliveryFeeCol, $rowIndex),
            'deduction_amount'  => SpreadsheetParser::getDecimal($sheet, $colMap, 'deduction amount', $rowIndex),
            'deduction_note'    => SpreadsheetParser::getString($sheet, $colMap, 'deduction note', $rowIndex) ?: null,
            'compensation'      => SpreadsheetParser::getDecimal($sheet, $colMap, 'driver compensate', $rowIndex),
            'compensation_note' => SpreadsheetParser::getString($sheet, $colMap, 'driver compensate note', $rowIndex) ?: null,
            'bonus_amount'      => SpreadsheetParser::getDecimal($sheet, $colMap, 'bonus amount', $rowIndex),
            'bonus_note'        => SpreadsheetParser::getString($sheet, $colMap, 'bonus note', $rowIndex) ?: null,
        ];
    }

    private function groupByDelegate(array $parsedRows): array
    {
        $grouped = [];
        foreach ($parsedRows as $row) {
            $id = $row['raw_driver_id'];
            if (! isset($grouped[$id])) {
                $grouped[$id] = [
                    'name'           => $row['raw_driver_name'],
                    'orders'         => 0,
                    'gross_fees'     => 0.0,
                    'deductions'     => 0.0,
                    'compensations'  => 0.0,
                    'bonus_total'    => 0.0,
                    'positive_bonus' => 0.0,
                ];
            }
            $grouped[$id]['orders']++;
            $grouped[$id]['gross_fees']     += $row['delivery_fee'];
            $grouped[$id]['deductions']     += $row['deduction_amount'];
            $grouped[$id]['compensations']  += $row['compensation'];
            $grouped[$id]['bonus_total']    += $row['bonus_amount'];
            $grouped[$id]['positive_bonus'] += max(0.0, $row['bonus_amount']);
        }
        return $grouped;
    }

    private function resolveDelegates(
        array $perDelegateSummary,
        MonthlyPeriod $period,
        int $userId,
        int &$newDelegatesCount
    ): array {
        $newDelegatesCount = 0;
        $driverIds         = array_column($perDelegateSummary, 'driver_id');

        $existingMap = $driverIds
            ? Delegate::withTrashed()->whereIn('national_id', $driverIds)->pluck('id', 'national_id')->all()
            : [];

        $delegateIdMap   = [];
        $defaultCityId   = DB::table('cities')->value('id');
        $chefzPlatformId = DB::table('platforms')->where('code', 'the-chefz')->value('id');

        foreach ($perDelegateSummary as $summary) {
            $driverId = $summary['driver_id'];

            if (isset($existingMap[$driverId])) {
                $delegateIdMap[$driverId] = $existingMap[$driverId];
                continue;
            }

            $delegate = Delegate::create([
                'delegate_code' => $driverId,
                'name'          => $summary['name'],
                'national_id'   => $driverId,
                'status'        => 'active',
                'city_id'       => $defaultCityId,
                'platform_id'   => $chefzPlatformId,
                'needs_review'  => true,
                'notes'         => 'تم الإنشاء تلقائياً خلال استيراد بيانات شيفز — يرجى مراجعة البيانات',
                'created_by'    => $userId,
            ]);

            $delegateIdMap[$driverId] = $delegate->id;
            $newDelegatesCount++;
        }

        return $delegateIdMap;
    }

    private function moveToPermanent(string $tempStoragePath, MonthlyPeriod $period, string $originalFilename): string
    {
        $year      = $period->year;
        $month     = str_pad((string) $period->month, 2, '0', STR_PAD_LEFT);
        $timestamp = now()->format('YmdHis');
        $dir       = "imports/{$year}/{$month}/chefz";

        Storage::makeDirectory($dir);
        $permanentPath = "{$dir}/{$timestamp}_{$originalFilename}";
        Storage::move($tempStoragePath, $permanentPath);

        return $permanentPath;
    }

    private function displayHeaderName(string $normalized): string
    {
        return match ($normalized) {
            'chef order id'        => 'Chef Order ID',
            'driver id'            => 'Driver ID',
            'driver name'          => 'Driver Name',
            'date'                 => 'Date',
            'driver delivery fees' => 'Driver Delivery Fees',
            default                => $normalized,
        };
    }
}
