<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllHeroesStats extends Model
{
    protected $fillable = [
        'player_id',
        'average_assists',
        'average_damage',
        'average_deaths',
        'average_eliminations',
        'average_healing',
        'games_lost',
        'games_played',
        'games_won',
        'hero_wins',
        'kda',
        'time_played',
        'winrate',
        'total_assists',
        'total_damage',
        'total_deaths',
        'total_eliminations',
        'total_healing',
        'assists',
        'defensive_assists',
        'offensive_assists',
        'healing_done',
        'assists_avg_per_10_min',
        'deaths_avg_per_10_min',
        'eliminations_avg_per_10_min',
        'final_blows_avg_per_10_min',
        'healing_done_avg_per_10_min',
        'hero_damage_done_avg_per_10_min',
        'objective_contest_time_avg_per_10_min',
        'objective_kills_avg_per_10_min',
        'objective_time_avg_per_10_min',
        'all_damage_done_most_in_game',
        'assists_most_in_game',
        'barrier_damage_done_most_in_game',
        'defensive_assists_most_in_game',
        'eliminations_most_in_game',
        'environmental_kills_most_in_game',
        'final_blows_most_in_game',
        'healing_done_most_in_game',
        'hero_damage_done_most_in_game',
        'kill_streak_best',
        'multikill_best',
        'objective_contest_time_most_in_game',
        'objective_kills_most_in_game',
        'objective_time_most_in_game',
        'offensive_assists_most_in_game',
        'solo_kills_most_in_game',
        'time_spent_on_fire_most_in_game',
        'damage_done',
        'deaths',
        'eliminations',
        'environmental_kills',
        'final_blows',
        'hero_damage_done',
        'multikills',
        'objective_contest_time',
        'objective_kills',
        'objective_time',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }
}
