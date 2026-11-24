<?php

namespace Database\Factories;

use App\Models\Hero;
use App\Models\HeroSpecificStat;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSpecificStat>
 */
class HeroSpecificStatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hero_id' => Hero::factory(),
            'player_id' => Profile::factory(),
            'stat_key' => fake()->unique()->slug(),
            'stat_value' => (string) fake()->numberBetween(0, 10000),
        ];
    }
}
