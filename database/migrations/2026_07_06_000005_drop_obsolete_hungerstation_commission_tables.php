<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the retired HungerStation commission-based tables.
 *
 * These tables were created by migrations 2026_07_05_000010 through
 * 2026_07_05_000021 for the per-order commission model. The project has
 * migrated to the FTR (Fixed Rate) contract model which uses:
 *   - hungerstation_ftr_import_batches
 *   - hungerstation_ftr_settlements
 *   - hungerstation_ftr_delegate_deductions
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop in reverse FK order
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('hungerstation_delegate_deductions');
        Schema::dropIfExists('hungerstation_delegate_settlements');
        Schema::dropIfExists('hungerstation_orders');
        Schema::dropIfExists('hungerstation_import_batches');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // These tables are permanently retired — no rollback.
    }
};
