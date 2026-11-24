<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_stat_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('player_id');
            $table->string('fingerprint', 64);
            $table->json('snapshot');
            $table->timestamp('captured_at');

            $table->foreign('player_id')
                ->references('player_id')
                ->on('profiles')
                ->cascadeOnDelete();
            $table->index(['player_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_stat_snapshots');
    }
};
