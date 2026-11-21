<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_stats', function (Blueprint $table) {
            $table->id();
            $table->string('player_id');
            $table->string('hero_id');
            $table->unsignedBigInteger('assists')->default(0);
            $table->unsignedBigInteger('defensive_assists')->default(0);
            $table->unsignedBigInteger('offensive_assists')->default(0);
            $table->unsignedBigInteger('healing_done')->default(0);
            $table->decimal('defensive_assists_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('offensive_assists_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('assists_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('deaths_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('eliminations_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('eliminations_per_life', 10, 2)->nullable();
            $table->decimal('final_blows_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('healing_done_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('hero_damage_done_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('all_damage_done_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('critical_hit_kills_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('critical_hits_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('objective_contest_time_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('objective_kills_avg_per_10_min', 12, 2)->nullable();
            $table->decimal('objective_time_avg_per_10_min', 12, 2)->nullable();
            $table->unsignedBigInteger('all_damage_done_most_in_game')->default(0);
            $table->unsignedBigInteger('all_damage_done_most_in_life')->default(0);
            $table->unsignedBigInteger('assists_most_in_game')->default(0);
            $table->unsignedBigInteger('barrier_damage_done_most_in_game')->default(0);
            $table->unsignedBigInteger('critical_hit_kills_most_in_game')->default(0);
            $table->unsignedBigInteger('critical_hits_most_in_game')->default(0);
            $table->unsignedBigInteger('critical_hits_most_in_life')->default(0);
            $table->unsignedBigInteger('defensive_assists_most_in_game')->default(0);
            $table->unsignedBigInteger('eliminations_most_in_game')->default(0);
            $table->unsignedBigInteger('eliminations_most_in_life')->default(0);
            $table->unsignedBigInteger('environmental_kills_most_in_game')->default(0);
            $table->unsignedBigInteger('final_blows_most_in_game')->default(0);
            $table->unsignedBigInteger('healing_done_most_in_game')->default(0);
            $table->unsignedBigInteger('hero_damage_done_most_in_game')->default(0);
            $table->unsignedBigInteger('hero_damage_done_most_in_life')->default(0);
            $table->unsignedBigInteger('kill_streak_best')->default(0);
            $table->unsignedBigInteger('multikill_best')->default(0);
            $table->unsignedBigInteger('objective_contest_time_most_in_game')->default(0);
            $table->unsignedBigInteger('objective_kills_most_in_game')->default(0);
            $table->unsignedBigInteger('objective_time_most_in_game')->default(0);
            $table->unsignedBigInteger('offensive_assists_most_in_game')->default(0);
            $table->unsignedBigInteger('solo_kills_most_in_game')->default(0);
            $table->unsignedBigInteger('time_spent_on_fire_most_in_game')->default(0);
            $table->decimal('weapon_accuracy_best_in_game', 8, 2)->nullable();
            $table->unsignedBigInteger('all_damage_done')->default(0);
            $table->unsignedBigInteger('deaths')->default(0);
            $table->unsignedBigInteger('eliminations')->default(0);
            $table->unsignedBigInteger('environmental_kills')->default(0);
            $table->decimal('environmental_kills_avg_per_10_min', 12, 2)->nullable();
            $table->unsignedBigInteger('final_blows')->default(0);
            $table->unsignedBigInteger('hero_damage_done')->default(0);
            $table->decimal('critical_hit_accuracy', 8, 2)->nullable();
            $table->unsignedBigInteger('critical_hit_kills')->default(0);
            $table->unsignedBigInteger('critical_hits')->default(0);
            $table->unsignedBigInteger('multikills')->default(0);
            $table->unsignedBigInteger('objective_contest_time')->default(0);
            $table->unsignedBigInteger('objective_kills')->default(0);
            $table->unsignedBigInteger('objective_time')->default(0);
            $table->decimal('weapon_accuracy', 8, 2)->nullable();
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->unsignedInteger('games_lost')->default(0);
            $table->unsignedInteger('hero_wins')->default(0);
            $table->unsignedBigInteger('time_played')->default(0);
            $table->decimal('win_percentage', 8, 2)->nullable();
            $table->unsignedBigInteger('self_healing')->default(0);
            $table->decimal('self_healing_avg_per_10_min', 12, 2)->nullable();
            $table->unsignedBigInteger('self_healing_most_in_game')->default(0);
            $table->timestamps();

            $table->foreign('player_id')->references('player_id')->on('profiles')->cascadeOnDelete();
            $table->foreign('hero_id')->references('hero_id')->on('heroes')->cascadeOnDelete();
            $table->unique(['player_id', 'hero_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_stats');
    }
};
