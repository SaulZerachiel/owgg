<?php

namespace Database\Factories;

use App\Models\Hero;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Hero>
 */
class HeroFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'hero_id' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'role_id' => Role::factory(),
            'hero_name' => $name,
        ];
    }
}
