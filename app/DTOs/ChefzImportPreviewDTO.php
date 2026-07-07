<?php

namespace App\DTOs;

/**
 * Carries all parsed/validated data from a Chefz xlsx parse pass.
 * Stored in session between upload and confirm steps.
 *
 * payout_number: 1 = First Payout, 2 = Second Payout.
 * isReplace: true when a completed batch for this payout already exists (will be deleted on confirm).
 * inFileDuplicates: rows whose order_id_platform appeared more than once in this file (first kept).
 * perDelegateSummary: aggregated per-delegate totals for this import.
 */
class ChefzImportPreviewDTO
{
    public function __construct(
        public readonly int    $payoutNumber,
        public readonly bool   $isReplace,
        public readonly int    $totalRows,
        public readonly int    $newRows,
        public readonly int    $inFileDuplicates,
        public readonly int    $uniqueDelegates,
        public readonly int    $knownCount,
        public readonly int    $willCreateCount,
        public readonly array  $parsedRows,
        public readonly array  $perDelegateSummary,
        public readonly array  $reconciliation,
        public readonly array  $warnings,
        public readonly array  $errors,
        public readonly bool   $isConfirmable,
        public readonly string $tempStoragePath,
        public readonly string $originalFilename,
        public readonly int    $fileSizeBytes,
    ) {}
}
