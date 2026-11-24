<?php

namespace Database\Seeders;

use App\Models\AiCall;
use App\Models\AllHeroesStats;
use App\Models\Hero;
use App\Models\HeroSpecificStat;
use App\Models\HeroStats;
use App\Models\Profile;
use App\Models\Rank;
use App\Models\Role;
use App\Models\RolesStats;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'battlenet_id' => 'demo-battlenet-id',
                'battletag' => 'Demo#1234',
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );

        $profile = Profile::query()->updateOrCreate(
            ['player_id' => 'demo-player'],
            [
                'user_id' => $user->id,
                'name' => 'Demo Player',
                'career_url' => 'https://overwatch.blizzard.com/career/demo-player',
                'blizzard_id' => 'Demo#1234',
                'last_updated_at' => now(),
                'is_public' => true,
            ],
        );

        AllHeroesStats::query()->updateOrCreate(
            ['player_id' => $profile->player_id],
            [
                'games_played' => 100,
                'games_won' => 55,
                'games_lost' => 45,
                'hero_wins' => 50,
                'total_eliminations' => 1200,
                'total_deaths' => 500,
                'winrate' => 55,
            ],
        );

        foreach (Role::query()->get() as $index => $role) {
            RolesStats::query()->updateOrCreate(
                ['roles_stats_id' => 'demo-roles-stats-'.$role->role_id],
                [
                    'role_id' => $role->role_id,
                    'player_id' => $profile->player_id,
                    'games_played' => 30,
                    'games_won' => 16,
                    'games_lost' => 14,
                    'total_eliminations' => 300 + $index * 50,
                    'winrate' => 53.33,
                ],
            );

            Rank::query()->updateOrCreate(
                ['rank_id' => 'demo-rank-'.$role->role_id],
                [
                    'player_id' => $profile->player_id,
                    'role_id' => $role->role_id,
                    'season' => 'demo-season',
                    'tier' => 'Gold',
                    'division' => 3,
                ],
            );
        }

        foreach (Hero::query()->get() as $hero) {
            HeroStats::query()->updateOrCreate(
                [
                    'player_id' => $profile->player_id,
                    'hero_id' => $hero->hero_id,
                ],
                [
                    'games_played' => 20,
                    'games_won' => 12,
                    'games_lost' => 8,
                    'hero_wins' => 10,
                    'eliminations' => 250,
                    'deaths' => 90,
                    'win_percentage' => 60,
                ],
            );

            HeroSpecificStat::query()->updateOrCreate(
                [
                    'hero_id' => $hero->hero_id,
                    'player_id' => $profile->player_id,
                    'stat_key' => 'demo-stat',
                ],
                ['stat_value' => '42'],
            );
        }

        AiCall::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'model' => 'demo-model',
            ],
            [
                'prompt_tokens' => 120,
                'completion_tokens' => 60,
                'cost' => 0.001,
                'duration_ms' => 850,
                'success' => true,
            ],
        );
    }
}
