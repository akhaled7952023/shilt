<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend batch_status enum to include 'deleted'
        DB::statement("ALTER TABLE hungerstation_import_batches MODIFY batch_status ENUM('active','archived','failed','deleted') NULL");

        // Add soft-delete audit fields for deleted batches (not using SoftDeletes trait — manual audit)
        DB::statement('ALTER TABLE hungerstation_import_batches
            ADD COLUMN deleted_at TIMESTAMP NULL AFTER imported_at,
            ADD COLUMN deleted_by BIGINT UNSIGNED NULL AFTER deleted_at,
            ADD COLUMN delete_reason TEXT NULL AFTER deleted_by,
            ADD CONSTRAINT hib_deleted_by_fk FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE hungerstation_import_batches
            DROP FOREIGN KEY hib_deleted_by_fk,
            DROP COLUMN delete_reason,
            DROP COLUMN deleted_by,
            DROP COLUMN deleted_at');

        DB::statement("ALTER TABLE hungerstation_import_batches MODIFY batch_status ENUM('active','archived','failed') NULL");
    }
};
