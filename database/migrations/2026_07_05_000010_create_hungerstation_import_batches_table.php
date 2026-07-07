<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hungerstation_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_period_id')->constrained('monthly_periods');
            $table->string('original_filename', 255)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('unique_delegates')->nullable();
            $table->unsignedInteger('new_delegates_created')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('import_duration_ms')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('batch_status', ['active', 'archived', 'failed'])->nullable();
            $table->unsignedTinyInteger('version_number')->default(1);
            $table->unsignedBigInteger('replaced_by_batch_id')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index('monthly_period_id');
            $table->foreign('replaced_by_batch_id')
                  ->references('id')
                  ->on('hungerstation_import_batches')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hungerstation_import_batches');
    }
};
