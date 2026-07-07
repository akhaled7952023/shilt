<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('delegate_id')->nullable()->constrained('delegates')->nullOnDelete();
            $table->foreignId('warning_type_id')->nullable()->constrained('warning_types')->nullOnDelete();
            $table->string('location', 200)->nullable();
            $table->date('date');
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('delegate_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_violations');
    }
};
