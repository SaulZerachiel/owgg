<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'role_id' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'role_name' => ucfirst($name),
            'role_icon' => null,
        ];
    }
}
