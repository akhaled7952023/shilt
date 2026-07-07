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
        Schema::create('delegates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('national_id', 20)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('password')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('restrict');
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('city_id');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegates');
    }
};
