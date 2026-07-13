<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                ->constrained('support_tickets')
                ->cascadeOnDelete();
            $table->foreignId('reply_id')->nullable()
                ->constrained('support_ticket_replies')
                ->nullOnDelete()
                ->comment('NULL = opening message attachment; otherwise linked to a reply');

            // Uploader
            $table->enum('uploader_type', ['delegate', 'admin']);
            $table->unsignedBigInteger('uploader_id')
                ->comment('delegate_id or user_id depending on uploader_type');

            // File metadata
            $table->string('original_filename', 255);
            $table->string('stored_path', 500)
                ->comment('Relative path inside Laravel private storage disk');
            $table->unsignedBigInteger('file_size')
                ->comment('Size in bytes');
            $table->string('mime_type', 100);

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('ticket_id', 'idx_sta_ticket');
            $table->index('reply_id',  'idx_sta_reply');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_attachments');
    }
};
