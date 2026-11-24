<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Rank;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Rank>
 */
class RankFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rank_id' => (string) Str::uuid(),
            'player_id' => Profile::factory(),
            'role_id' => Role::factory(),
            'season' => fake()->numerify('Season ##'),
            'tier' => fake()->randomElement(['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Master']),
            'division' => fake()->numberBetween(1, 5),
        ];
    }
}
