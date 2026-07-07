<?php

namespace Database\Seeders;

use App\Models\Delegate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ONE-TIME DEVELOPMENT BOOTSTRAP — NOT a production feature.
 *
 * Reads hanger.xlsx and theshifz.xlsx, extracts all unique delegates by
 * National ID, and populates the delegates table. Safe to re-run: it
 * skips any delegate whose national_id already exists (including soft-deleted).
 *
 * Run once:
 *   php artisan db:seed --class=DelegateBootstrapSeeder
 */
class DelegateBootstrapSeeder extends Seeder
{
    private const HS_FILE    = '.specify/phases/final_phase/hanger.xlsx';
    private const CHEFZ_FILE = '.specify/phases/final_phase/theshifz.xlsx';

    // Column letters in both Excel files
    private const COL_NAME = 'B'; // Driver Name
    private const COL_ID   = 'E'; // Driver ID = National ID / Iqama (10-digit) — used as both national_id and delegate_code

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║      DELEGATE BOOTSTRAP SEEDER — Dev Only        ║');
        $this->command->info('║  Safe to re-run. Never overwrites existing data. ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');
        $this->command->info('');

        $hsFile    = base_path(self::HS_FILE);
        $chefzFile = base_path(self::CHEFZ_FILE);

        foreach (['HungerStation' => $hsFile, 'Chefz' => $chefzFile] as $label => $path) {
            if (! file_exists($path)) {
                $this->command->error("File not found [{$label}]: {$path}");
                return;
            }
        }

        // ── 1. Extract unique delegates from each file ───────────────────────
        $hsDelegates    = $this->extractDelegates($hsFile);
        $chefzDelegates = $this->extractDelegates($chefzFile);

        $this->command->line("  HungerStation unique delegates : " . count($hsDelegates));
        $this->command->line("  Chefz unique delegates         : " . count($chefzDelegates));

        // ── 2. Resolve platform IDs from DB ──────────────────────────────────
        $platforms = DB::table('platforms')->pluck('id', 'code');
        $hsId      = $platforms->get('hungerstation');
        $chefzId   = $platforms->get('the-chefz');

        if (! $hsId || ! $chefzId) {
            $this->command->error('Platforms not found in DB. Run PlatformSeeder first.');
            return;
        }

        // ── 3. Resolve default city ───────────────────────────────────────────
        $defaultCityId = $this->resolveDefaultCityId();
        $this->command->line("  Default city_id                : {$defaultCityId}");

        // ── 4. Merge by National ID ───────────────────────────────────────────
        // Structure: national_id => ['name' => string, 'hs' => bool, 'chefz' => bool]
        $merged = [];

        foreach ($hsDelegates as $nationalId => $name) {
            $merged[$nationalId] = ['name' => $name, 'hs' => true, 'chefz' => false];
        }
        foreach ($chefzDelegates as $nationalId => $name) {
            if (isset($merged[$nationalId])) {
                $merged[$nationalId]['chefz'] = true;
            } else {
                $merged[$nationalId] = ['name' => $name, 'hs' => false, 'chefz' => true];
            }
        }

        $this->command->line("  Total unique national IDs      : " . count($merged));
        $bothCount = collect($merged)->filter(fn($d) => $d['hs'] && $d['chefz'])->count();
        $this->command->line("  On both platforms              : {$bothCount} (→ assigned to HungerStation)");
        $this->command->info('');

        // ── 5. Insert delegates ───────────────────────────────────────────────
        $created = 0;
        $skipped = 0;

        foreach ($merged as $nationalId => $data) {
            // Check including soft-deleted to prevent ghost duplicates
            if (Delegate::withTrashed()->where('national_id', $nationalId)->exists()) {
                $this->command->line("  <fg=yellow>SKIP</>  {$nationalId} — {$data['name']}");
                $skipped++;
                continue;
            }

            // Determine platform_id:
            //   - HS only   → HungerStation
            //   - Chefz only → The Chefz
            //   - Both       → HungerStation (primary priority; future: many-to-many)
            if ($data['hs']) {
                $platformId = $hsId;
            } else {
                $platformId = $chefzId;
            }

            $platformTag = match (true) {
                $data['hs'] && $data['chefz'] => 'HS+CHEFZ→HS',
                $data['hs']                   => 'HS',
                default                       => 'CHEFZ',
            };

            Delegate::create([
                'delegate_code' => $nationalId,
                'name'          => $data['name'],
                'national_id'   => $nationalId,
                'status'        => 'active',
                'city_id'       => $defaultCityId,
                'platform_id'   => $platformId,
                'bank_name'     => null,
                'iban'          => null,
                'hire_date'     => null,
                'notes'         => 'تم الإنشاء من Bootstrap — يرجى مراجعة البيانات وتكملتها',
                'created_by'    => null,
            ]);

            $this->command->line("  <fg=green>CREATE</> [{$platformTag}] {$nationalId} — {$data['name']}");
            $created++;
        }

        $this->command->info('');
        $this->command->info("✓ Done. Created: {$created} | Skipped (already exist): {$skipped}");
        $this->command->info('');
        $this->command->warn('Next step: Open dashboard → Delegates and complete the missing data for each delegate.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Parse an xlsx file and return unique delegates as [national_id => name].
     *
     * Uses ZipArchive + SimpleXML — no external library required.
     */
    private function extractDelegates(string $file): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \RuntimeException("Cannot open Excel file: {$file}");
        }

        // Shared strings table (xlsx stores text as indexes into this table)
        $sharedStrings = [];
        $idx = $zip->locateName('xl/sharedStrings.xml');
        if ($idx !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($idx));
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string) $si->t;
                } else {
                    // Rich text: concatenate all <r><t> segments
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();

        $delegates = [];
        $isHeader  = true;

        foreach ($sheetXml->sheetData->row as $row) {
            // Skip header row
            if ($isHeader) {
                $isHeader = false;
                continue;
            }

            $cells = [];
            foreach ($row->c as $cell) {
                // Cell reference like "A1", "B2" — extract column letter(s)
                $col  = rtrim((string) $cell['r'], '0123456789');
                $type = (string) $cell['t'];
                $val  = isset($cell->v) ? (string) $cell->v : '';

                // Dereference shared string index
                if ($type === 's' && $val !== '') {
                    $val = $sharedStrings[(int) $val] ?? $val;
                }

                $cells[$col] = trim($val);
            }

            $nationalId = $cells[self::COL_ID]   ?? '';
            $name       = $cells[self::COL_NAME] ?? '';

            // Store first occurrence only (duplicates within a file are the same delegate)
            if ($nationalId && ! isset($delegates[$nationalId])) {
                $delegates[$nationalId] = $name;
            }
        }

        return $delegates;
    }

    /**
     * Get the default city_id for new delegates.
     * Reads from system_settings.import_default_city_id, falls back to first city.
     */
    private function resolveDefaultCityId(): ?int
    {
        $setting = DB::table('system_settings')
            ->where('key', 'import_default_city_id')
            ->value('value');

        if ($setting !== null) {
            $cityId = (int) $setting;
            if ($cityId > 0 && DB::table('cities')->where('id', $cityId)->exists()) {
                return $cityId;
            }
        }

        // Fallback: first city in the table
        return DB::table('cities')->value('id');
    }
}
