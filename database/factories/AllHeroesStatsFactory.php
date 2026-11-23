<?php

namespace Database\Factories;

use App\Models\AllHeroesStats;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AllHeroesStats>
 */
class AllHeroesStatsFactory extends Factory
{
    public function definition(): array
    {
        $gamesPlayed = fake()->numberBetween(1, 200);
        $gamesWon = fake()->numberBetween(0, $gamesPlayed);

        return [
            'player_id' => Profile::factory(),
            'average_assists' => fake()->randomFloat(2, 0, 30),
            'average_damage' => fake()->randomFloat(2, 0, 30000),
            'average_deaths' => fake()->randomFloat(2, 0, 20),
            'average_eliminations' => fake()->randomFloat(2, 0, 40),
            'average_healing' => fake()->randomFloat(2, 0, 30000),
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'games_lost' => $gamesPlayed - $gamesWon,
            'hero_wins' => fake()->numberBetween(0, $gamesWon),
            'kda' => fake()->randomFloat(2, 0, 10),
            'time_played' => fake()->numberBetween(60, 100000),
            'winrate' => round($gamesWon / $gamesPlayed * 100, 2),
        ];
    }
}
