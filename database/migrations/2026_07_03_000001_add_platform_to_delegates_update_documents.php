<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make delegates.national_id nullable (it was NOT NULL before)
        DB::statement('ALTER TABLE `delegates` MODIFY `national_id` VARCHAR(20) NULL');

        Schema::table('delegates', function (Blueprint $table) {
            $table->foreignId('platform_id')->nullable()->constrained('platforms')->nullOnDelete()->after('city_id');
            $table->string('platform_delegate_number', 100)->nullable()->after('platform_id');
        });

        // Make delegate_documents.file_path nullable (Iqama has no file)
        DB::statement('ALTER TABLE `delegate_documents` MODIFY `file_path` VARCHAR(255) NULL');

        Schema::table('delegate_documents', function (Blueprint $table) {
            $table->string('document_number', 100)->nullable()->after('document_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
            $table->dropColumn(['platform_id', 'platform_delegate_number']);
        });

        Schema::table('delegate_documents', function (Blueprint $table) {
            $table->dropColumn('document_number');
        });

        DB::statement('ALTER TABLE `delegate_documents` MODIFY `file_path` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `delegates` MODIFY `national_id` VARCHAR(20) NOT NULL');
    }
};
