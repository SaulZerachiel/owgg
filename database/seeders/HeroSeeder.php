<?php

namespace Database\Seeders;

use App\Models\Hero;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'dva' => ['role_id' => 'tank', 'hero_name' => 'D.Va'],
            'soldier-76' => ['role_id' => 'damage', 'hero_name' => 'Soldier: 76'],
            'mercy' => ['role_id' => 'support', 'hero_name' => 'Mercy'],
        ] as $heroId => $attributes) {
            Hero::query()->updateOrCreate(
                ['hero_id' => $heroId],
                $attributes,
            );
        }
    }
}
