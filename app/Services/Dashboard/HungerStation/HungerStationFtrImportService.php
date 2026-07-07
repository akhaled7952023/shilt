<?php

namespace App\Services\Dashboard\HungerStation;

use App\DTOs\HungerStationFtrImportPreviewDTO;
use App\Exceptions\Import\ImportHeaderException;
use App\Models\Delegate;
use App\Models\HungerStationFtrDelegateDeduction;
use App\Models\HungerStationFtrImportBatch;
use App\Models\HungerStationFtrSettlement;
use App\Models\MonthlyPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HungerStationFtrImportService
{
    // Minimum keywords required in RLVL header row (case-insensitive partial match)
    private const REQUIRED_HEADER_KEYWORDS = [
        'rider'    => 'rider_id',
        'basic'    => 'basic_payment',
        'distance' => 'distance_payment',
        'balance'  => 'rider_balance',
    ];

    // All 19 RLVL columns mapped by keyword to internal field name
    private const COLUMN_MAP = [
        'rider'       => 'rider_id_platform',      // col A
        'total order' => 'total_orders',            // col C (matches "total orders" or "completed")
        'completed'   => 'total_orders',            // fallback keyword
        'city pay'    => 'city_payment',            // col D
        'basic'       => 'basic_payment',           // col E
        'acceptance r'=> 'acceptance_rate_penalties',// col F
        'contact'     => 'contact_rate_penalties',  // col G
        'stack'       => 'stacking_deduction',      // col H
        'declin'      => 'declined_penalties',      // col I
        'late'        => 'late_penalty',            // col J
        'no show spec'=> 'no_show_penalty_special_cities', // col L — must check BEFORE 'no show'
        'no show'     => 'no_show_penalty',         // col K
        'daily'       => 'daily_acceptance_rate_penalty', // col M
        'distance'    => 'distance_payment',        // col N
        'missed'      => 'missed_days_penalty',     // col O
        'segment'     => 'segment_payment',         // col P
        'courier basic'=> 'courier_basic_payment',  // col Q
        'courier scor' => 'courier_scoring_payment',// col R
        'balance'     => 'rider_balance',           // col S
    ];

    // ── Public API ────────────────────────────────────────────────────────────────

    public function parseAndValidate(
        string $localFilePath,
        int    $monthlyPeriodId,
        string $tempStoragePath,
        string $originalFilename,
        int    $fileSizeBytes,
    ): HungerStationFtrImportPreviewDTO {
        $spreadsheet = IOFactory::load($localFilePath);

        // Find the RLVL sheet — try by name first, then by index 1
        $sheet = $spreadsheet->getSheetByName('RLVL')
            ?? $spreadsheet->getSheetByName('rlvl')
            ?? ($spreadsheet->getSheetCount() > 1 ? $spreadsheet->getSheet(1) : null)
            ?? $spreadsheet->getActiveSheet();

        $headerMap = $this->buildFtrColumnMap($sheet);
        $this->validateRequiredHeaders($headerMap);

        // Pre-load delegates indexed by hungerstation_rider_id
        $delegateMap = Delegate::whereNotNull('hungerstation_rider_id')
            ->select(['id', 'name', 'hungerstation_rider_id'])
            ->get()
            ->keyBy('hungerstation_rider_id');

        $isReplace = HungerStationFtrImportBatch::where('monthly_period_id', $monthlyPeriodId)
            ->where('status', 'completed')
            ->exists();

        $matchedRiders   = [];
        $unmatchedRiders = [];
        $warnings        = [];
        $highestRow      = $sheet->getHighestDataRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $parsed = $this->readRow($sheet, $headerMap, $row);
            if ($parsed === null) {
                continue;
            }

            $riderId = $parsed['rider_id_platform'];

            if ($delegateMap->has($riderId)) {
                $delegate   = $delegateMap->get($riderId);
                $penalties  = $this->sumPenalties($parsed);
                $estimatedNet = round(
                    $parsed['distance_payment'] - $penalties - abs($parsed['rider_balance']),
                    2
                );

                $matchedRiders[] = array_merge($parsed, [
                    'delegate_id'   => $delegate->id,
                    'delegate_name' => $delegate->name,
                    'estimated_net' => $estimatedNet,
                    'total_penalties' => $penalties,
                ]);
            } else {
                $unmatchedRiders[] = $riderId;
            }

            // Warn on non-zero informational columns
            foreach (['city_payment', 'segment_payment', 'courier_basic_payment', 'courier_scoring_payment'] as $col) {
                if (isset($parsed[$col]) && abs((float) $parsed[$col]) > 0) {
                    $warnings[] = "الصف {$row}: العمود {$col} = {$parsed[$col]} (غير صفر — قد يحتاج مراجعة)";
                }
            }
        }

        $totals = $this->buildTotals($matchedRiders, $unmatchedRiders, $sheet, $headerMap, $highestRow);

        return new HungerStationFtrImportPreviewDTO(
            totalRiders:      count($matchedRiders) + count($unmatchedRiders),
            matchedCount:     count($matchedRiders),
            unmatchedCount:   count($unmatchedRiders),
            matchedRiders:    $matchedRiders,
            unmatchedRiders:  $unmatchedRiders,
            totals:           $totals,
            warnings:         $warnings,
            isConfirmable:    count($matchedRiders) > 0,
            isReplace:        $isReplace,
            tempStoragePath:  $tempStoragePath,
            originalFilename: $originalFilename,
            fileSizeBytes:    $fileSizeBytes,
        );
    }

    public function confirm(
        HungerStationFtrImportPreviewDTO $dto,
        MonthlyPeriod $period,
        int $userId
    ): HungerStationFtrImportBatch {
        $startTime = microtime(true);

        return DB::transaction(function () use ($dto, $period, $userId, $startTime) {
            // Replace semantics: delete existing batch + settlements for this period
            $this->deletePeriodFtrData($period);

            $permanentPath = $this->moveToPermanent($dto->tempStoragePath, $period, $dto->originalFilename);

            $batch = HungerStationFtrImportBatch::create([
                'monthly_period_id'    => $period->id,
                'original_filename'    => $dto->originalFilename,
                'file_path'            => $permanentPath,
                'file_size_bytes'      => $dto->fileSizeBytes,
                'total_riders'         => $dto->totalRiders,
                'matched_delegates'    => $dto->matchedCount,
                'unmatched_riders'     => $dto->unmatchedCount,
                'basic_payment_total'  => $dto->totals['basic_payment'],
                'distance_payment_total' => $dto->totals['distance_payment'],
                'rider_balance_total'  => $dto->totals['rider_balance'],
                'status'               => 'processing',
                'imported_by'          => $userId,
                'imported_at'          => now(),
            ]);

            foreach ($dto->matchedRiders as $rider) {
                $settlement = HungerStationFtrSettlement::create([
                    'monthly_period_id'                => $period->id,
                    'delegate_id'                      => $rider['delegate_id'],
                    'import_batch_id'                  => $batch->id,
                    'rider_id_platform'                => $rider['rider_id_platform'],
                    'total_orders'                     => (int) ($rider['total_orders'] ?? 0),
                    'basic_payment'                    => $rider['basic_payment'],
                    'acceptance_rate_penalties'        => $rider['acceptance_rate_penalties'],
                    'contact_rate_penalties'           => $rider['contact_rate_penalties'],
                    'stacking_deduction'               => $rider['stacking_deduction'],
                    'declined_penalties'               => $rider['declined_penalties'],
                    'late_penalty'                     => $rider['late_penalty'],
                    'no_show_penalty'                  => $rider['no_show_penalty'],
                    'no_show_penalty_special_cities'   => $rider['no_show_penalty_special_cities'],
                    'daily_acceptance_rate_penalty'    => $rider['daily_acceptance_rate_penalty'],
                    'distance_payment'                 => $rider['distance_payment'],
                    'missed_days_penalty'              => $rider['missed_days_penalty'],
                    'city_payment'                     => $rider['city_payment'] ?? 0,
                    'segment_payment'                  => $rider['segment_payment'] ?? 0,
                    'courier_basic_payment'            => $rider['courier_basic_payment'] ?? 0,
                    'courier_scoring_payment'          => $rider['courier_scoring_payment'] ?? 0,
                    'rider_balance'                    => $rider['rider_balance'],
                    'total_platform_penalties'         => 0,
                    'housing_allowance'                => 0,
                    'company_deductions_total'         => 0,
                    'net_salary'                       => 0,
                    'is_locked'                        => false,
                    'created_by'                       => $userId,
                ]);

                $settlement->recalculate();
            }

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $batch->update([
                'status'             => 'completed',
                'import_duration_ms' => $durationMs,
            ]);

            return $batch;
        });
    }

    /**
     * Delete all FTR data for a period (used for replace semantics and period deletion).
     * Preserves manually-entered deductions only if the settlement is replaced rather than deleted.
     * For replace semantics we delete everything and re-import clean.
     */
    public function deletePeriodFtrData(MonthlyPeriod $period): void
    {
        $batchIds = HungerStationFtrImportBatch::where('monthly_period_id', $period->id)
            ->pluck('id')
            ->toArray();

        if (empty($batchIds)) {
            return;
        }

        $settlementIds = HungerStationFtrSettlement::where('monthly_period_id', $period->id)
            ->pluck('id')
            ->toArray();

        if ($settlementIds) {
            DB::table('hungerstation_ftr_delegate_deductions')
                ->whereIn('settlement_id', $settlementIds)
                ->delete();

            DB::table('hungerstation_ftr_settlements')
                ->whereIn('id', $settlementIds)
                ->delete();
        }

        foreach (HungerStationFtrImportBatch::whereIn('id', $batchIds)->get() as $batch) {
            if ($batch->file_path) {
                Storage::delete($batch->file_path);
            }
        }

        DB::table('hungerstation_ftr_import_batches')
            ->whereIn('id', $batchIds)
            ->delete();
    }

    // ── Private helpers ───────────────────────────────────────────────────────────

    /**
     * Build a column map: internal_field_name => column_letter.
     * Uses keyword-based partial matching on lowercased header text.
     */
    private function buildFtrColumnMap(Worksheet $sheet): array
    {
        $highestCol = $sheet->getHighestDataColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        $headers = [];
        for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $cellValue = trim((string) $sheet->getCell($colLetter . '1')->getValue());
            if ($cellValue !== '') {
                $headers[$colLetter] = strtolower($cellValue);
            }
        }

        $result = [];

        // Priority order matters: check 'no show spec' before 'no show', 'courier basic' before 'courier scor'
        $prioritizedMap = [
            'no show spec'  => 'no_show_penalty_special_cities',
            'courier basic' => 'courier_basic_payment',
            'courier scor'  => 'courier_scoring_payment',
            'city pay'      => 'city_payment',
            'acceptance r'  => 'acceptance_rate_penalties',
            'total order'   => 'total_orders',
            'completed'     => 'total_orders',
            'contact'       => 'contact_rate_penalties',
            'stack'         => 'stacking_deduction',
            'declin'        => 'declined_penalties',
            'late'          => 'late_penalty',
            'no show'       => 'no_show_penalty',
            'daily'         => 'daily_acceptance_rate_penalty',
            'missed'        => 'missed_days_penalty',
            'segment'       => 'segment_payment',
            'rider'         => 'rider_id_platform',
            'basic'         => 'basic_payment',
            'distance'      => 'distance_payment',
            'balance'       => 'rider_balance',
        ];

        foreach ($headers as $colLetter => $headerLower) {
            foreach ($prioritizedMap as $keyword => $fieldName) {
                if (isset($result[$fieldName])) {
                    continue; // already mapped
                }
                if (str_contains($headerLower, $keyword)) {
                    $result[$fieldName] = $colLetter;
                    break;
                }
            }
        }

        return $result;
    }

    private function validateRequiredHeaders(array $headerMap): void
    {
        $required = [
            'rider_id_platform' => 'Rider ID',
            'basic_payment'     => 'Basic Payment',
            'distance_payment'  => 'Distance Payment',
            'rider_balance'     => 'Rider Balance',
        ];

        foreach ($required as $field => $displayName) {
            if (! isset($headerMap[$field])) {
                throw new ImportHeaderException($displayName);
            }
        }
    }

    private function readRow(Worksheet $sheet, array $headerMap, int $row): ?array
    {
        $riderId = trim((string) $sheet->getCell(($headerMap['rider_id_platform'] ?? 'A') . $row)->getValue());
        if ($riderId === '' || $riderId === '0') {
            return null;
        }

        // Normalize: strip .0 suffix from numeric IDs (e.g. "1234567.0" → "1234567")
        $riderId = preg_replace('/\.0+$/', '', $riderId);

        $getNum = function (string $field) use ($sheet, $headerMap, $row): float {
            if (! isset($headerMap[$field])) {
                return 0.0;
            }
            $val = $sheet->getCell($headerMap[$field] . $row)->getCalculatedValue();
            return round(abs((float) $val), 4);
        };

        // Rider balance: stored as-is from sheet (positive = owes money; we take abs when deducting)
        $balanceRaw = isset($headerMap['rider_balance'])
            ? (float) $sheet->getCell($headerMap['rider_balance'] . $row)->getCalculatedValue()
            : 0.0;

        return [
            'rider_id_platform'                => $riderId,
            'total_orders'                     => (int) $getNum('total_orders'),
            'basic_payment'                    => $getNum('basic_payment'),
            'acceptance_rate_penalties'        => $getNum('acceptance_rate_penalties'),
            'contact_rate_penalties'           => $getNum('contact_rate_penalties'),
            'stacking_deduction'               => $getNum('stacking_deduction'),
            'declined_penalties'               => $getNum('declined_penalties'),
            'late_penalty'                     => $getNum('late_penalty'),
            'no_show_penalty'                  => $getNum('no_show_penalty'),
            'no_show_penalty_special_cities'   => $getNum('no_show_penalty_special_cities'),
            'daily_acceptance_rate_penalty'    => $getNum('daily_acceptance_rate_penalty'),
            'distance_payment'                 => $getNum('distance_payment'),
            'missed_days_penalty'              => $getNum('missed_days_penalty'),
            'city_payment'                     => $getNum('city_payment'),
            'segment_payment'                  => $getNum('segment_payment'),
            'courier_basic_payment'            => $getNum('courier_basic_payment'),
            'courier_scoring_payment'          => $getNum('courier_scoring_payment'),
            'rider_balance'                    => round(abs($balanceRaw), 2),
        ];
    }

    private function sumPenalties(array $parsed): float
    {
        return round(
            (float) ($parsed['acceptance_rate_penalties'] ?? 0)
            + (float) ($parsed['contact_rate_penalties'] ?? 0)
            + (float) ($parsed['declined_penalties'] ?? 0)
            + (float) ($parsed['late_penalty'] ?? 0)
            + (float) ($parsed['no_show_penalty'] ?? 0)
            + (float) ($parsed['no_show_penalty_special_cities'] ?? 0)
            + (float) ($parsed['daily_acceptance_rate_penalty'] ?? 0)
            + (float) ($parsed['missed_days_penalty'] ?? 0),
            2
        );
    }

    private function buildTotals(array $matched, array $unmatched, Worksheet $sheet, array $headerMap, int $highestRow): array
    {
        // Aggregate from matched riders (most reliable — avoids re-reading sheet)
        $basicTotal    = array_sum(array_column($matched, 'basic_payment'));
        $distanceTotal = array_sum(array_column($matched, 'distance_payment'));
        $balanceTotal  = array_sum(array_column($matched, 'rider_balance'));
        $stackingTotal = array_sum(array_column($matched, 'stacking_deduction'));
        $penaltyTotal  = array_sum(array_column($matched, 'total_penalties'));

        return [
            'basic_payment'    => round($basicTotal, 2),
            'distance_payment' => round($distanceTotal, 2),
            'rider_balance'    => round($balanceTotal, 2),
            'stacking'         => round($stackingTotal, 2),
            'penalties'        => round($penaltyTotal, 2),
        ];
    }

    private function moveToPermanent(string $tempPath, MonthlyPeriod $period, string $originalFilename): string
    {
        $year      = $period->year;
        $month     = str_pad((string) $period->month, 2, '0', STR_PAD_LEFT);
        $timestamp = now()->format('YmdHis');
        $dir       = "imports/{$year}/{$month}/hungerstation_ftr";

        Storage::makeDirectory($dir);
        $permanentPath = "{$dir}/{$timestamp}_{$originalFilename}";
        Storage::move($tempPath, $permanentPath);

        return $permanentPath;
    }
}
