<?php

namespace Database\Factories;

use App\Models\AiCall;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiCall>
 */
class AiCallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'model' => fake()->randomElement(['openai/gpt-4o-mini', 'anthropic/claude-3.5-haiku']),
            'prompt_tokens' => fake()->numberBetween(10, 2000),
            'completion_tokens' => fake()->numberBetween(10, 1000),
            'cost' => fake()->randomFloat(8, 0, 0.1),
            'duration_ms' => fake()->numberBetween(100, 10000),
            'success' => true,
            'error_code' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'success' => false,
            'error_code' => fake()->word(),
        ]);
    }
}
