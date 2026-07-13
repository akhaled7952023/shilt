<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_email_settings', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)
                ->comment('Display name for this recipient');
            $table->string('email', 255)->unique();

            $table->json('subscribed_categories')
                ->comment('Array of category slugs this address receives');

            $table->tinyInteger('is_active')->default(1);

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_email_settings');
    }
};
