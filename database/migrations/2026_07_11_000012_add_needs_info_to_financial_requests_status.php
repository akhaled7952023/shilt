<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE financial_requests
             MODIFY COLUMN status ENUM('pending','approved','rejected','needs_info')
             NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        DB::statement("UPDATE financial_requests SET status = 'pending' WHERE status = 'needs_info'");
        DB::statement(
            "ALTER TABLE financial_requests
             MODIFY COLUMN status ENUM('pending','approved','rejected')
             NOT NULL DEFAULT 'pending'"
        );
    }
};
