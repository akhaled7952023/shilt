<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reusable worksheet-parsing helpers shared by all import services.
 */
class SpreadsheetParser
{
    /**
     * Build a lowercase column-name → letter map from the header row.
     * E.g. ['driver id' => 'B', 'order id' => 'C', ...]
     */
    public static function buildColumnMap(Worksheet $sheet): array
    {
        $colMap      = [];
        $rowIterator = $sheet->getRowIterator(1, 1);
        $headerRow   = $rowIterator->current();

        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            $val = $cell->getValue();
            if ($val !== null && trim((string) $val) !== '') {
                $colMap[strtolower(trim((string) $val))] = $cell->getColumn();
            }
        }

        return $colMap;
    }

    /**
     * Parse a date cell — handles both Excel serial and string dates.
     */
    public static function parseDate(Worksheet $sheet, string $col, int $row): ?string
    {
        $cell  = $sheet->getCell($col . $row);
        $value = $cell->getValue();

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value) && $value > 0) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);
                return $dt->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return (new \DateTime(trim((string) $value)))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Normalize IDs that arrive from Excel as floats (e.g. 123456.0 → '123456').
     */
    public static function normalizeId(string $raw): string
    {
        if (is_numeric($raw) && str_contains($raw, '.')) {
            return (string) (int) round((float) $raw);
        }
        return $raw;
    }

    /**
     * Get a trimmed string value from a named column (returns '' if column absent or cell empty).
     */
    public static function getString(Worksheet $sheet, array $colMap, string $header, int $row): string
    {
        $col = $colMap[$header] ?? null;
        if (! $col) {
            return '';
        }
        return trim((string) ($sheet->getCell($col . $row)->getCalculatedValue() ?? ''));
    }

    /**
     * Get a decimal value from a named column (returns 0.0 if absent or empty).
     */
    public static function getDecimal(Worksheet $sheet, array $colMap, string $header, int $row): float
    {
        $col = $colMap[$header] ?? null;
        if (! $col) {
            return 0.0;
        }
        $val = $sheet->getCell($col . $row)->getCalculatedValue();
        if ($val === null || trim((string) $val) === '') {
            return 0.0;
        }
        return (float) $val;
    }
}
