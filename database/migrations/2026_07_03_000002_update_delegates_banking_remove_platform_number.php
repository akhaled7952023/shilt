<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropColumn('platform_delegate_number');
            $table->string('bank_name', 150)->nullable()->after('profile_photo');
            $table->string('iban', 34)->nullable()->after('bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->string('platform_delegate_number', 100)->nullable()->after('platform_id');
            $table->dropColumn(['bank_name', 'iban']);
        });
    }
};
