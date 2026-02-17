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
        Schema::create('email_response_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segnalazione_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sent', 'received']);
            $table->text('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('external_message_id')->nullable()->index();
            $table->string('in_reply_to')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['sent', 'delivered', 'opened', 'bounced', 'complained'])->default('sent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_response_messages');
    }
};
