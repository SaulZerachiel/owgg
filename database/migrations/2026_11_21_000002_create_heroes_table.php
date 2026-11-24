<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('heroes') && Schema::hasColumn('heroes', 'hero_id')) {
            return;
        }

        $legacyHeroes = [];
        if (Schema::hasTable('heroes')) {
            if (Schema::hasTable('heroes_legacy')) {
                throw new RuntimeException('Both legacy and current heroes tables exist; refusing to overwrite either.');
            }

            $legacyHeroes = DB::table('heroes')->get(['slug', 'name', 'role'])->all();
            Schema::rename('heroes', 'heroes_legacy');
        }

        if (! Schema::hasTable('heroes')) {
            Schema::create('heroes', function (Blueprint $table) {
                $table->string('hero_id')->primary();
                $table->string('role_id');
                $table->string('hero_name');
                $table->timestamps();

                $table->foreign('role_id')->references('role_id')->on('roles')->cascadeOnDelete();
            });
        }

        $roles = [];
        $heroes = [];
        $now = now();

        foreach ($legacyHeroes as $legacyHero) {
            if (! is_string($legacyHero->slug) || ! is_string($legacyHero->name) || ! is_string($legacyHero->role)) {
                throw new RuntimeException('The legacy heroes table contains invalid rows.');
            }

            $roleId = Str::slug($legacyHero->role);
            if ($roleId === 'dps') {
                $roleId = 'damage';
            }

            $roles[$roleId] = [
                'role_id' => $roleId,
                'role_name' => match ($roleId) {
                    'tank' => 'Tank',
                    'damage' => 'Damage',
                    'support' => 'Support',
                    default => Str::title(str_replace('-', ' ', $roleId)),
                },
                'role_icon' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $heroes[] = [
                'hero_id' => $legacyHero->slug,
                'role_id' => $roleId,
                'hero_name' => $legacyHero->name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($roles !== []) {
            DB::table('roles')->upsert(array_values($roles), ['role_id'], ['role_name', 'updated_at']);
            DB::table('heroes')->upsert($heroes, ['hero_id'], ['role_id', 'hero_name', 'updated_at']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes');

        if (Schema::hasTable('heroes_legacy')) {
            Schema::rename('heroes_legacy', 'heroes');
        }
    }
};
