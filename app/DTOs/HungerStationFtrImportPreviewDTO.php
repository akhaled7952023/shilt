<?php

namespace App\DTOs;

/**
 * Immutable preview payload for the HungerStation FTR import pipeline.
 *
 * matchedRiders   — riders whose rider_id matched a delegate in the system
 * unmatchedRiders — riders not found in delegates table (import skips them)
 * totals          — financial aggregates from the RLVL sheet
 * warnings        — non-fatal issues (e.g. non-zero informational columns)
 */
readonly class HungerStationFtrImportPreviewDTO
{
    public function __construct(
        public int    $totalRiders,
        public int    $matchedCount,
        public int    $unmatchedCount,
        public array  $matchedRiders,     // [rider_id, delegate_id, delegate_name, distance_payment, estimated_net, ...]
        public array  $unmatchedRiders,   // [rider_id]
        public array  $totals,            // [basic_payment, distance_payment, rider_balance, stacking, penalties]
        public array  $warnings,
        public bool   $isConfirmable,     // true if at least one rider matched
        public bool   $isReplace,         // true if replacing an existing batch
        public string $tempStoragePath,
        public string $originalFilename,
        public int    $fileSizeBytes,
    ) {}
}
