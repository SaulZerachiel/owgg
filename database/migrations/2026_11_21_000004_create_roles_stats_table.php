<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_stats', function (Blueprint $table) {
            $table->string('roles_stats_id')->primary();
            $table->string('role_id');
            $table->string('player_id');
            $table->decimal('average_assists', 12, 2)->nullable();
            $table->decimal('average_damage', 12, 2)->nullable();
            $table->decimal('average_deaths', 12, 2)->nullable();
            $table->decimal('average_eliminations', 12, 2)->nullable();
            $table->decimal('average_healing', 12, 2)->nullable();
            $table->unsignedInteger('games_lost')->default(0);
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->decimal('kda', 10, 2)->nullable();
            $table->unsignedBigInteger('time_played')->default(0);
            $table->decimal('winrate', 8, 2)->nullable();
            $table->unsignedBigInteger('total_assists')->default(0);
            $table->unsignedBigInteger('total_damage')->default(0);
            $table->unsignedBigInteger('total_deaths')->default(0);
            $table->unsignedBigInteger('total_eliminations')->default(0);
            $table->unsignedBigInteger('total_healing')->default(0);
            $table->timestamps();

            $table->foreign('role_id')->references('role_id')->on('roles')->cascadeOnDelete();
            $table->foreign('player_id')->references('player_id')->on('profiles')->cascadeOnDelete();
            $table->unique(['role_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_stats');
    }
};
