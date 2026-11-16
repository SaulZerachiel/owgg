<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $profile = $user->profiles()->create([
            'player_id' => 'demo-player',
            'name' => 'Demo Player',
            'is_public' => true,
        ]);

        $heroes = collect([
            ['slug' => 'ana', 'name' => 'Ana', 'role' => 'support'],
            ['slug' => 'soldier-76', 'name' => 'Soldier: 76', 'role' => 'damage'],
            ['slug' => 'winston', 'name' => 'Winston', 'role' => 'tank'],
        ])->mapWithKeys(fn (array $hero): array => [
            $hero['slug'] => Hero::create($hero),
        ]);

        $match = $profile->matches()->create([
            'external_id' => 'demo-match',
            'mode' => 'competitive',
            'won' => true,
            'duration_seconds' => 602,
            'played_at' => now()->subHour(),
        ]);

        $match->heroStats()->create([
            'hero_id' => $heroes['ana']->id,
            'eliminations' => 18,
            'deaths' => 4,
            'damage' => 4200,
            'healing' => 8900,
            'time_played_seconds' => 602,
        ]);

        $analysis = $match->analysis()->create([
            'summary' => 'Bonne gestion des soins et des positions pendant la partie.',
            'strengths' => ['Soins réguliers', 'Bonne survie'],
            'weaknesses' => ['Peut améliorer la précision offensive'],
            'score' => 78,
            'model' => null,
        ]);

        $analysis->recommendations()->create([
            'title' => 'Travailler la précision',
            'content' => 'Consacrez quelques minutes à viser les cibles prioritaires.',
            'priority' => 2,
        ]);
    }
}
