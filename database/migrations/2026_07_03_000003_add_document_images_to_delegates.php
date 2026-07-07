<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->string('iqama_image')->nullable()->after('profile_photo');
            $table->string('driving_license_image')->nullable()->after('iqama_image');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropColumn(['iqama_image', 'driving_license_image']);
        });
    }
};
