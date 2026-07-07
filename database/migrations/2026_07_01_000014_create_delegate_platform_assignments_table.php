<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delegate_platform_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegate_id')->constrained('delegates')->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('platforms')->onDelete('restrict');
            $table->string('platform_delegate_id', 100);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['delegate_id', 'platform_id', 'is_active'], 'dpa_delegate_platform_active_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegate_platform_assignments');
    }
};
