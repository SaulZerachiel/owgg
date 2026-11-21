<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroStats extends Model
{
    protected $fillable = [
        'player_id',
        'hero_id',
        'assists',
        'defensive_assists',
        'offensive_assists',
        'healing_done',
        'defensive_assists_avg_per_10_min',
        'defensive_assists_most_in_game',
        'offensive_assists_avg_per_10_min',
        'offensive_assists_most_in_game',
        'assists_avg_per_10_min',
        'deaths_avg_per_10_min',
        'eliminations_avg_per_10_min',
        'eliminations_per_life',
        'final_blows_avg_per_10_min',
        'healing_done_avg_per_10_min',
        'hero_damage_done_avg_per_10_min',
        'all_damage_done_avg_per_10_min',
        'critical_hit_kills_avg_per_10_min',
        'critical_hits_avg_per_10_min',
        'objective_contest_time_avg_per_10_min',
        'objective_kills_avg_per_10_min',
        'objective_time_avg_per_10_min',
        'all_damage_done_most_in_game',
        'all_damage_done_most_in_life',
        'assists_most_in_game',
        'barrier_damage_done_most_in_game',
        'critical_hit_kills_most_in_game',
        'critical_hits_most_in_game',
        'critical_hits_most_in_life',
        'defensive_assists_most_in_game',
        'eliminations_most_in_game',
        'eliminations_most_in_life',
        'environmental_kills_most_in_game',
        'final_blows_most_in_game',
        'healing_done_most_in_game',
        'hero_damage_done_most_in_game',
        'hero_damage_done_most_in_life',
        'kill_streak_best',
        'multikill_best',
        'objective_contest_time_most_in_game',
        'objective_kills_most_in_game',
        'objective_time_most_in_game',
        'offensive_assists_most_in_game',
        'solo_kills_most_in_game',
        'time_spent_on_fire_most_in_game',
        'weapon_accuracy_best_in_game',
        'all_damage_done',
        'deaths',
        'eliminations',
        'environmental_kills',
        'environmental_kills_avg_per_10_min',
        'final_blows',
        'hero_damage_done',
        'critical_hit_accuracy',
        'critical_hit_kills',
        'critical_hits',
        'multikills',
        'objective_contest_time',
        'objective_kills',
        'objective_time',
        'weapon_accuracy',
        'games_played',
        'games_won',
        'games_lost',
        'hero_wins',
        'time_played',
        'win_percentage',
        'self_healing',
        'self_healing_avg_per_10_min',
        'self_healing_most_in_game',
    ];

    public function hero(): BelongsTo
    {
        return $this->belongsTo(Hero::class, 'hero_id', 'hero_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'player_id', 'player_id');
    }
}
