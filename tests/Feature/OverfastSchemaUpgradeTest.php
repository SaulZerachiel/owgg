<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('legacy hero rows are preserved when the OverFast schema is applied', function () {
    Schema::drop('hero_specific_stats');
    Schema::drop('hero_stats');
    Schema::drop('heroes');
    Schema::rename('heroes_legacy', 'heroes');

    DB::table('heroes')->insert([
        'slug' => 'soldier-76',
        'name' => 'Soldier: 76',
        'role' => 'DPS',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = require database_path('migrations/2026_11_21_000002_create_heroes_table.php');
    $migration->up();

    expect(DB::table('heroes')->where('hero_id', 'soldier-76')->value('hero_name'))
        ->toBe('Soldier: 76')
        ->and(DB::table('heroes')->where('hero_id', 'soldier-76')->value('role_id'))
        ->toBe('damage')
        ->and(DB::table('roles')->where('role_id', 'damage')->value('role_name'))
        ->toBe('Damage');
});
