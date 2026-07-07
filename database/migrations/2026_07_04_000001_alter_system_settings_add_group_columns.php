<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('group', 50)->nullable()->default('general')->after('description');
            $table->tinyInteger('is_public')->unsigned()->default(0)->after('group');
            $table->unsignedBigInteger('updated_by')->nullable()->after('is_public');
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Add 'decimal' to the existing type ENUM
        DB::statement(
            "ALTER TABLE system_settings MODIFY COLUMN type
             ENUM('string','integer','boolean','json','decimal')
             NOT NULL DEFAULT 'string'"
        );
    }

    public function down(): void
    {
        // Remove 'decimal' from ENUM before dropping columns (safest order)
        DB::statement(
            "ALTER TABLE system_settings MODIFY COLUMN type
             ENUM('string','integer','boolean','json')
             NOT NULL DEFAULT 'string'"
        );

        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['group', 'is_public', 'updated_by']);
        });
    }
};
