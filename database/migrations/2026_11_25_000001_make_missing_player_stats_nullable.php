<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array{integer: array<int, string>, bigInteger: array<int, string>}>
     */
    private const STAT_COLUMNS = [
        'roles_stats' => [
            'integer' => ['games_lost', 'games_played', 'games_won'],
            'bigInteger' => [
                'time_played',
                'total_assists',
                'total_damage',
                'total_deaths',
                'total_eliminations',
                'total_healing',
            ],
        ],
        'all_heroes_stats' => [
            'integer' => ['games_lost', 'games_played', 'games_won', 'hero_wins'],
            'bigInteger' => [
                'time_played',
                'total_assists',
                'total_damage',
                'total_deaths',
                'total_eliminations',
                'total_healing',
                'assists',
                'defensive_assists',
                'offensive_assists',
                'healing_done',
                'all_damage_done_most_in_game',
                'assists_most_in_game',
                'barrier_damage_done_most_in_game',
                'defensive_assists_most_in_game',
                'eliminations_most_in_game',
                'environmental_kills_most_in_game',
                'final_blows_most_in_game',
                'healing_done_most_in_game',
                'hero_damage_done_most_in_game',
                'kill_streak_best',
                'multikill_best',
                'objective_contest_time_most_in_game',
                'objective_kills_most_in_game',
                'objective_time_most_in_game',
                'offensive_assists_most_in_game',
                'solo_kills_most_in_game',
                'time_spent_on_fire_most_in_game',
                'damage_done',
                'deaths',
                'eliminations',
                'environmental_kills',
                'final_blows',
                'hero_damage_done',
                'multikills',
                'objective_contest_time',
                'objective_kills',
                'objective_time',
            ],
        ],
        'hero_stats' => [
            'integer' => ['games_played', 'games_won', 'games_lost', 'hero_wins'],
            'bigInteger' => [
                'assists',
                'defensive_assists',
                'offensive_assists',
                'healing_done',
                'all_damage_done_most_in_game',
                'all_damage_done_most_in_life',
                'assists_most_in_game',
                'barrier_damage_done_most_in_game',
                'critical_hit_kills_most_in_game',
                'critical_hits_most_in_game',
                'critical_hits_most_in_life',
                'defensive_assists_most_in_game',
                'eliminations_most_in_game',
                'eliminations_most_in_life',
                'environmental_kills_most_in_game',
                'final_blows_most_in_game',
                'healing_done_most_in_game',
                'hero_damage_done_most_in_game',
                'hero_damage_done_most_in_life',
                'kill_streak_best',
                'multikill_best',
                'objective_contest_time_most_in_game',
                'objective_kills_most_in_game',
                'objective_time_most_in_game',
                'offensive_assists_most_in_game',
                'solo_kills_most_in_game',
                'time_spent_on_fire_most_in_game',
                'all_damage_done',
                'deaths',
                'eliminations',
                'environmental_kills',
                'final_blows',
                'hero_damage_done',
                'critical_hit_kills',
                'critical_hits',
                'multikills',
                'objective_contest_time',
                'objective_kills',
                'objective_time',
                'time_played',
                'self_healing',
                'self_healing_most_in_game',
            ],
        ],
    ];

    public function up(): void
    {
        foreach (self::STAT_COLUMNS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                $this->makeNullable($table, $columns);
            });
        }

        DB::table('users')->update(['stats_synced_at' => null]);
    }

    public function down(): void
    {
        foreach (self::STAT_COLUMNS as $tableName => $columns) {
            foreach ([...$columns['integer'], ...$columns['bigInteger']] as $column) {
                DB::table($tableName)->whereNull($column)->update([$column => 0]);
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns['integer'] as $column) {
                    $table->unsignedInteger($column)->default(0)->change();
                }

                foreach ($columns['bigInteger'] as $column) {
                    $table->unsignedBigInteger($column)->default(0)->change();
                }
            });
        }
    }

    /**
     * @param  array{integer: array<int, string>, bigInteger: array<int, string>}  $columns
     */
    private function makeNullable(Blueprint $table, array $columns): void
    {
        foreach ($columns['integer'] as $column) {
            $table->unsignedInteger($column)->nullable()->default(null)->change();
        }

        foreach ($columns['bigInteger'] as $column) {
            $table->unsignedBigInteger($column)->nullable()->default(null)->change();
        }
    }
};
