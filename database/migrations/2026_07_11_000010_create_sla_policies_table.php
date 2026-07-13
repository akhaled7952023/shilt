<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();

            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->unique();

            $table->unsignedSmallInteger('first_response_hours')
                ->comment('Hours from ticket creation until admin must send the first reply');
            $table->unsignedSmallInteger('resolution_hours')
                ->comment('Hours from ticket creation until ticket must reach resolved status');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
