<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Role;
use App\Models\RolesStats;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RolesStats>
 */
class RolesStatsFactory extends Factory
{
    public function definition(): array
    {
        $gamesPlayed = fake()->numberBetween(1, 100);
        $gamesWon = fake()->numberBetween(0, $gamesPlayed);

        return [
            'roles_stats_id' => (string) Str::uuid(),
            'role_id' => Role::factory(),
            'player_id' => Profile::factory(),
            'average_assists' => fake()->randomFloat(2, 0, 30),
            'average_damage' => fake()->randomFloat(2, 0, 30000),
            'average_deaths' => fake()->randomFloat(2, 0, 20),
            'average_eliminations' => fake()->randomFloat(2, 0, 40),
            'average_healing' => fake()->randomFloat(2, 0, 30000),
            'games_lost' => $gamesPlayed - $gamesWon,
            'games_played' => $gamesPlayed,
            'games_won' => $gamesWon,
            'kda' => fake()->randomFloat(2, 0, 10),
            'time_played' => fake()->numberBetween(60, 50000),
            'winrate' => round($gamesWon / $gamesPlayed * 100, 2),
            'total_assists' => fake()->numberBetween(0, 10000),
            'total_damage' => fake()->numberBetween(0, 1000000),
            'total_deaths' => fake()->numberBetween(0, 10000),
            'total_eliminations' => fake()->numberBetween(0, 20000),
            'total_healing' => fake()->numberBetween(0, 1000000),
        ];
    }
}
