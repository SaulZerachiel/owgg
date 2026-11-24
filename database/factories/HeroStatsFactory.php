<?php

namespace Database\Factories;

use App\Models\Hero;
use App\Models\HeroStats;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroStats>
 */
class HeroStatsFactory extends Factory
{
    public function definition(): array
    {
        $gamesPlayed = fake()->numberBetween(1, 100);
        $gamesWon = fake()->numberBetween(0, $gamesPlayed);

        return [
            'player_id' => Profile::factory(),
            'hero_id' => Hero::factory(),
            'assists' => fake()->numberBetween(0, 1000),
            'deaths' => fake()->numberBetween(0, 1000),
            'eliminations' => fake()->numberBetween(0, 2000),
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'games_lost' => $gamesPlayed - $gamesWon,
            'hero_wins' => fake()->numberBetween(0, $gamesWon),
            'time_played' => fake()->numberBetween(60, 50000),
            'win_percentage' => round($gamesWon / $gamesPlayed * 100, 2),
        ];
    }
}
