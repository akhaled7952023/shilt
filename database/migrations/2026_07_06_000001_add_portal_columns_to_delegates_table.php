<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->string('portal_password', 255)->nullable()->after('password');
            $table->boolean('portal_enabled')->default(false)->after('portal_password');
            $table->boolean('portal_first_login')->default(true)->after('portal_enabled');
            $table->timestamp('last_portal_login')->nullable()->after('portal_first_login');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropColumn(['portal_password', 'portal_enabled', 'portal_first_login', 'last_portal_login']);
        });
    }
};
