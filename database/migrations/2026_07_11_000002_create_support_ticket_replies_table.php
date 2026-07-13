<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('support_tickets')
                ->cascadeOnDelete();

            // Author — one of the two FK columns is populated based on author_type
            $table->enum('author_type', ['delegate', 'admin']);
            $table->unsignedBigInteger('author_delegate_id')->nullable()
                ->comment('Populated when author_type = delegate');
            $table->unsignedBigInteger('author_user_id')->nullable()
                ->comment('Populated when author_type = admin');

            // Content
            $table->text('content');
            $table->tinyInteger('is_internal_note')->default(0)
                ->comment('1 = admin-only note; delegate cannot see this reply');

            $table->timestamps();

            // FK constraints on author columns (nullable, so no cascade needed)
            $table->foreign('author_delegate_id')->references('id')->on('delegates');
            $table->foreign('author_user_id')->references('id')->on('users');

            // Indexes
            $table->index('ticket_id', 'idx_str_ticket');
            $table->index(['ticket_id', 'created_at'], 'idx_str_ticket_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
    }
};
