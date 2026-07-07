<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing FK, make column nullable, re-add FK with ON DELETE SET NULL
        // so archiving/deleting a batch does not orphan settlements that have manual deductions.
        $fkName = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'hungerstation_delegate_settlements'
              AND COLUMN_NAME = 'import_batch_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ")[0]->CONSTRAINT_NAME ?? null;

        if ($fkName) {
            DB::statement("ALTER TABLE hungerstation_delegate_settlements DROP FOREIGN KEY `{$fkName}`");
        }

        DB::statement('ALTER TABLE hungerstation_delegate_settlements
            MODIFY import_batch_id BIGINT UNSIGNED NULL');

        DB::statement('ALTER TABLE hungerstation_delegate_settlements
            ADD CONSTRAINT hds_import_batch_fk
            FOREIGN KEY (import_batch_id)
            REFERENCES hungerstation_import_batches(id)
            ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE hungerstation_delegate_settlements DROP FOREIGN KEY hds_import_batch_fk');
        DB::statement('ALTER TABLE hungerstation_delegate_settlements MODIFY import_batch_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE hungerstation_delegate_settlements
            ADD CONSTRAINT hungerstation_delegate_settlements_import_batch_id_foreign
            FOREIGN KEY (import_batch_id) REFERENCES hungerstation_import_batches(id)');
    }
};
