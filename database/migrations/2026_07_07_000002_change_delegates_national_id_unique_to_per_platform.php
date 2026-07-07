<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            // Global uniqueness prevented the same Iqama number from being used
            // across different platforms (e.g. a driver on both HungerStation and Chefz).
            // Replace with a composite constraint so uniqueness is enforced per platform only.
            $table->dropUnique('delegates_national_id_unique');
            $table->unique(['platform_id', 'national_id'], 'delegates_platform_national_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropUnique('delegates_platform_national_id_unique');
            $table->unique('national_id', 'delegates_national_id_unique');
        });
    }
};
