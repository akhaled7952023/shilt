<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_ftr_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->string('original_filename', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedTinyInteger('total_riders')->default(0);
            $table->unsignedTinyInteger('matched_delegates')->default(0);
            $table->unsignedTinyInteger('unmatched_riders')->default(0);
            $table->decimal('basic_payment_total', 14, 2)->default(0);
            $table->decimal('distance_payment_total', 14, 2)->default(0);
            $table->decimal('rider_balance_total', 14, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('import_duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('imported_by')->constrained('users');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index('monthly_period_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_ftr_import_batches');
    }
};
