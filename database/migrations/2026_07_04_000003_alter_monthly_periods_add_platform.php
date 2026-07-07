<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('platform_id')->nullable()->after('id');
            $table->string('label', 50)->nullable()->after('month');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');

            $table->foreign('platform_id')->references('id')->on('platforms')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->dropUnique(['year', 'month']);
            $table->unique(['platform_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::table('monthly_periods', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
            $table->dropForeign(['created_by']);
            $table->dropUnique(['platform_id', 'year', 'month']);
            $table->dropColumn(['platform_id', 'label', 'created_by']);
            $table->unique(['year', 'month']);
        });
    }
};
