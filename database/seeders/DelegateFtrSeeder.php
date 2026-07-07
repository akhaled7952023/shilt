<?php

namespace Database\Seeders;

use App\Models\Delegate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Bulk-seed delegates from the actual FTR and Chefz Excel source files.
 *
 * HS delegates: read from RLVL sheet of the FTR invoice file.
 *   - hungerstation_rider_id = rider_id from column A
 *   - delegate_code = "HS-{rider_id}"
 *   - national_id   = generated fake 10-digit Saudi ID
 *   - portal login  : delegate_code / password: Hs12345!
 *
 * Chefz delegates: read from theshifz.xlsx.
 *   - delegate_code = national_id = Driver ID (column E)
 *   - name          = Driver Name (column B, real name from file)
 *   - portal login  : delegate_code / password: Chefz123!
 *
 * Safe to re-run: skips existing delegates (by rider_id for HS, by national_id for Chefz).
 * Run: php artisan db:seed --class=DelegateFtrSeeder
 */
class DelegateFtrSeeder extends Seeder
{
    private const FTR_FILE   = '.specify/phases/final_phase/shilt_logistic_ftr_Invoice Issue May 2026.xlsx';
    private const CHEFZ_FILE = '.specify/phases/final_phase/theshifz.xlsx';

    private const HS_DEFAULT_PASSWORD    = 'Hs12345!';
    private const CHEFZ_DEFAULT_PASSWORD = 'Chefz123!';

    private array $arabicNames = [
        'محمد عبدالله', 'أحمد خالد', 'عمر إبراهيم', 'سعد يوسف', 'علي ناصر',
        'فيصل هاشم', 'طارق وليد', 'ماجد بدر', 'سلطان جابر', 'حسن منصور',
        'رائد نواف', 'زياد راشد', 'تركي بلال', 'مصطفى صالح', 'عادل سعيد',
        'خالد عمر', 'أنس ربيع', 'وائل سامي', 'أيمن حمدي', 'باسل فؤاد',
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║       DELEGATE FTR SEEDER — Dev/Staging          ║');
        $this->command->info('║  Creates delegates from FTR + Chefz source files.║');
        $this->command->info('║  Safe to re-run. Skips existing delegates.       ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');
        $this->command->info('');

        $platforms   = DB::table('platforms')->pluck('id', 'code');
        $hsId        = $platforms->get('hungerstation');
        $chefzId     = $platforms->get('the-chefz');
        $defaultCity = DB::table('cities')->value('id');

        if (!$hsId || !$chefzId) {
            $this->command->error('Platforms not found. Run PlatformSeeder first.');
            return;
        }

        $ftrPath   = base_path(self::FTR_FILE);
        $chefzPath = base_path(self::CHEFZ_FILE);

        $hsCreated    = $this->seedHs($ftrPath, $hsId, $defaultCity);
        $chefzCreated = $this->seedChefz($chefzPath, $chefzId, $defaultCity);

        $this->command->info('');
        $this->command->info("✓ Done. HS: {$hsCreated} created | Chefz: {$chefzCreated} created");
        $this->command->info('');
        $this->command->warn('Default portal passwords:');
        $this->command->warn('  HungerStation: delegate_code = "HS-{RiderID}" / password = "' . self::HS_DEFAULT_PASSWORD . '"');
        $this->command->warn('  Chefz:         delegate_code = national_id (Driver ID) / password = "' . self::CHEFZ_DEFAULT_PASSWORD . '"');
        $this->command->warn('  Delegates will be prompted to change password on first login.');
        $this->command->info('');
    }

    // ── HungerStation ─────────────────────────────────────────────────────────

    private function seedHs(string $ftrPath, int $hsId, ?int $defaultCity): int
    {
        if (!file_exists($ftrPath)) {
            $this->command->warn('[HS] FTR file not found: ' . $ftrPath);
            $this->command->warn('[HS] Falling back to hungerstation_ftr_settlements table...');
            return $this->seedHsFromDb($hsId, $defaultCity);
        }

        $this->command->line('[HS] Reading Rider IDs from: ' . basename($ftrPath));

        try {
            $spreadsheet = IOFactory::load($ftrPath);
        } catch (\Exception $e) {
            $this->command->error('[HS] Cannot open FTR file: ' . $e->getMessage());
            return 0;
        }

        $sheet = $spreadsheet->getSheetByName('RLVL')
            ?? $spreadsheet->getSheetByName('rlvl')
            ?? ($spreadsheet->getSheetCount() > 1 ? $spreadsheet->getSheet(1) : null)
            ?? $spreadsheet->getActiveSheet();

        $maxRow  = $sheet->getHighestDataRow();
        $riderIds = [];

        // Row 1 is the header; data starts at row 2
        for ($row = 2; $row <= $maxRow; $row++) {
            $riderId = trim((string) $sheet->getCell('A' . $row)->getValue());
            $riderId = preg_replace('/\.0+$/', '', $riderId); // strip ".0" from numeric
            if ($riderId !== '' && $riderId !== '0') {
                $riderIds[$riderId] = true;
            }
        }

        $this->command->line('[HS] Found ' . count($riderIds) . ' unique Rider IDs in RLVL sheet');

        return $this->createHsDelegates(array_keys($riderIds), $hsId, $defaultCity);
    }

    private function seedHsFromDb(int $hsId, ?int $defaultCity): int
    {
        $riderIds = DB::table('hungerstation_ftr_settlements')
            ->whereNotNull('rider_id_platform')
            ->distinct()
            ->pluck('rider_id_platform')
            ->toArray();

        $this->command->line('[HS] Found ' . count($riderIds) . ' unique Rider IDs in settlements table');
        return $this->createHsDelegates($riderIds, $hsId, $defaultCity);
    }

    private function createHsDelegates(array $riderIds, int $hsId, ?int $defaultCity): int
    {
        $created = 0;

        foreach ($riderIds as $riderId) {
            $riderId = (string) $riderId;
            if ($riderId === '') continue;

            if (Delegate::withTrashed()->where('hungerstation_rider_id', $riderId)->exists()) {
                $this->command->line("  <fg=yellow>SKIP</>  HS Rider ID: {$riderId} (already registered)");
                continue;
            }

            $delegateCode = 'HS-' . $riderId;

            // Handle delegate_code collision
            while (Delegate::withTrashed()->where('delegate_code', $delegateCode)->exists()) {
                $delegateCode = 'HS-' . $riderId . '-' . rand(10, 99);
            }

            $nationalId = $this->generateUniqueNationalId();
            $fakeName   = $this->arabicNames[array_rand($this->arabicNames)];
            $fakePhone  = '05' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);

            Delegate::create([
                'delegate_code'          => $delegateCode,
                'name'                   => $fakeName,
                'national_id'            => $nationalId,
                'phone'                  => $fakePhone,
                'city_id'                => $defaultCity,
                'platform_id'            => $hsId,
                'hungerstation_rider_id' => $riderId,
                'status'                 => 'active',
                'portal_enabled'         => true,
                'portal_password'        => Hash::make(self::HS_DEFAULT_PASSWORD),
                'portal_first_login'     => true,
                'notes'                  => 'مُنشأ تلقائياً — يرجى تكملة البيانات',
                'created_by'             => null,
            ]);

            $this->command->line("  <fg=green>CREATE</> HS | Rider: {$riderId} | Code: {$delegateCode} | {$fakeName}");
            $created++;
        }

        return $created;
    }

    // ── Chefz ─────────────────────────────────────────────────────────────────

    private function seedChefz(string $chefzPath, int $chefzId, ?int $defaultCity): int
    {
        if (!file_exists($chefzPath)) {
            $this->command->warn('[Chefz] File not found: ' . $chefzPath);
            return 0;
        }

        $this->command->line('[Chefz] Reading delegates from: ' . basename($chefzPath));

        try {
            $spreadsheet = IOFactory::load($chefzPath);
        } catch (\Exception $e) {
            $this->command->error('[Chefz] Cannot open file: ' . $e->getMessage());
            return 0;
        }

        $sheet  = $spreadsheet->getActiveSheet();
        $maxRow = $sheet->getHighestDataRow();

        // Detect header row — find row with "Driver ID" or "Driver Name"
        $headerRow   = 1;
        $colName     = 'B'; // Driver Name
        $colDriverId = 'E'; // Driver ID (national_id for Chefz)

        // Auto-detect columns — prefer specific keywords; once a specific match is found, don't
        // overwrite it with a generic match (e.g. "team name" / "group name" must not steal colName).
        $colNameLocked     = false;
        $colDriverIdLocked = false;
        $highestCol        = $sheet->getHighestDataColumn();

        foreach (range('A', $highestCol) as $col) {
            $header = strtolower(trim((string) $sheet->getCell($col . $headerRow)->getValue()));

            if (!$colNameLocked) {
                if (str_contains($header, 'driver name')) {
                    $colName       = $col;
                    $colNameLocked = true;
                } elseif (str_contains($header, 'name')) {
                    $colName = $col; // tentative; can be overridden by 'driver name'
                }
            }

            if (!$colDriverIdLocked) {
                if (str_contains($header, 'driver id')) {
                    $colDriverId       = $col;
                    $colDriverIdLocked = true;
                } elseif (str_contains($header, 'id') && !str_contains($header, 'date')) {
                    $colDriverId = $col; // tentative
                }
            }
        }

        // Collect unique delegates: driver_id => name
        $delegates = [];
        for ($row = 2; $row <= $maxRow; $row++) {
            $driverId = trim((string) $sheet->getCell($colDriverId . $row)->getValue());
            $driverId = preg_replace('/\.0+$/', '', $driverId);

            if ($driverId === '' || $driverId === '0') continue;

            if (!isset($delegates[$driverId])) {
                $name = trim((string) $sheet->getCell($colName . $row)->getValue());
                $delegates[$driverId] = $name ?: $this->arabicNames[array_rand($this->arabicNames)];
            }
        }

        $this->command->line('[Chefz] Found ' . count($delegates) . ' unique delegates in file');

        $created = 0;
        foreach ($delegates as $driverId => $name) {
            $driverId = (string) $driverId;

            if (Delegate::withTrashed()->where('national_id', $driverId)->exists()) {
                $this->command->line("  <fg=yellow>SKIP</>  Chefz ID: {$driverId} (already registered)");
                continue;
            }

            // Handle delegate_code collision (delegate_code = national_id for Chefz)
            if (Delegate::withTrashed()->where('delegate_code', $driverId)->exists()) {
                $this->command->line("  <fg=yellow>SKIP</>  Chefz code: {$driverId} (delegate_code collision)");
                continue;
            }

            $fakePhone = '05' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);

            Delegate::create([
                'delegate_code'   => $driverId,
                'name'            => $name,
                'national_id'     => $driverId,
                'phone'           => $fakePhone,
                'city_id'         => $defaultCity,
                'platform_id'     => $chefzId,
                'status'          => 'active',
                'portal_enabled'  => true,
                'portal_password' => Hash::make(self::CHEFZ_DEFAULT_PASSWORD),
                'portal_first_login' => true,
                'notes'           => 'مُنشأ تلقائياً من ملف شيفز — يرجى تكملة البيانات',
                'created_by'      => null,
            ]);

            $this->command->line("  <fg=green>CREATE</> Chefz | ID: {$driverId} | {$name}");
            $created++;
        }

        return $created;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateUniqueNationalId(): string
    {
        do {
            $id = (rand(0, 1) ? '1' : '2') . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Delegate::withTrashed()->where('national_id', $id)->exists());

        return $id;
    }
}
