<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->string('hungerstation_rider_id', 20)
                ->nullable()
                ->unique()
                ->after('national_id')
                ->comment('7-digit HungerStation platform Rider ID used for FTR import matching');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropUnique(['hungerstation_rider_id']);
            $table->dropColumn('hungerstation_rider_id');
        });
    }
};
