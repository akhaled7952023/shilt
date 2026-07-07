<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->date('date');
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('completed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance');
    }
};
