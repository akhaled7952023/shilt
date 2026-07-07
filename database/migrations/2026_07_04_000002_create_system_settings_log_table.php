<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings_log', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');

            $table->foreign('changed_by')->references('id')->on('users');
            $table->index('setting_key');
            $table->index(['changed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings_log');
    }
};
