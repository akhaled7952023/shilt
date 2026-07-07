<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delegate_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delegate_id');
            $table->string('type', 60);           // settlement_published | password_reset | portal_enabled | portal_disabled | announcement
            $table->string('title', 255);
            $table->text('body')->nullable();
            $table->json('data')->nullable();     // { period_id, period_label, ... }
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('delegate_id')->references('id')->on('delegates')->cascadeOnDelete();
            $table->index(['delegate_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegate_notifications');
    }
};
