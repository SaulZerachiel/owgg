<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('conversation_id', 36);
            $table->foreign('conversation_id')
                ->references('id')
                ->on('chat_conversations')
                ->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
