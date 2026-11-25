<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MATCHUPS = [
        ['winston', 'bastion', 'counter', -2],
        ['winston', 'reaper', 'counter', -2],
        ['winston', 'dva', 'counter', -1],
        ['winston', 'mauga', 'counter', -2],
        ['winston', 'widowmaker', 'counter', 2],
        ['winston', 'genji', 'counter', 2],
        ['winston', 'zenyatta', 'counter', 2],
        ['winston', 'ana', 'counter', 1],
        ['winston', 'tracer', 'synergy', 2],
        ['winston', 'ana', 'synergy', 2],
        ['bastion', 'winston', 'counter', 2],
        ['bastion', 'reinhardt', 'counter', 2],
        ['bastion', 'zarya', 'counter', 1],
        ['bastion', 'genji', 'counter', -2],
        ['bastion', 'ana', 'counter', -2],
        ['bastion', 'sigma', 'counter', -2],
        ['bastion', 'baptiste', 'synergy', 2],
        ['reinhardt', 'bastion', 'counter', -2],
        ['reinhardt', 'pharah', 'counter', -2],
        ['reinhardt', 'orisa', 'counter', -1],
        ['reinhardt', 'zarya', 'counter', 1],
        ['reinhardt', 'soldier-76', 'counter', 1],
        ['reinhardt', 'lucio', 'synergy', 2],
        ['reinhardt', 'ana', 'synergy', 2],
        ['dva', 'zarya', 'counter', -2],
        ['dva', 'symmetra', 'counter', -2],
        ['dva', 'pharah', 'counter', 2],
        ['dva', 'bastion', 'counter', 1],
        ['dva', 'widowmaker', 'counter', 2],
        ['dva', 'winston', 'counter', 1],
        ['zarya', 'dva', 'counter', 2],
        ['zarya', 'genji', 'counter', 2],
        ['zarya', 'orisa', 'counter', 1],
        ['zarya', 'bastion', 'counter', -1],
        ['zarya', 'reinhardt', 'counter', -1],
        ['zarya', 'pharah', 'counter', -2],
        ['pharah', 'soldier-76', 'counter', -2],
        ['pharah', 'cassidy', 'counter', -2],
        ['pharah', 'ashe', 'counter', -2],
        ['pharah', 'widowmaker', 'counter', -2],
        ['pharah', 'reinhardt', 'counter', 2],
        ['pharah', 'junkrat', 'counter', 2],
        ['pharah', 'mercy', 'synergy', 2],
        ['genji', 'bastion', 'counter', 2],
        ['genji', 'widowmaker', 'counter', 2],
        ['genji', 'winston', 'counter', -2],
        ['genji', 'zarya', 'counter', -2],
        ['genji', 'moira', 'counter', -2],
        ['genji', 'ana', 'synergy', 2],
        ['ana', 'roadhog', 'counter', 2],
        ['ana', 'mauga', 'counter', 2],
        ['ana', 'bastion', 'counter', 2],
        ['ana', 'kiriko', 'counter', -2],
        ['ana', 'winston', 'counter', -1],
        ['ana', 'tracer', 'counter', -2],
        ['ana', 'genji', 'synergy', 2],
        ['ana', 'winston', 'synergy', 2],
        ['kiriko', 'ana', 'counter', 2],
        ['kiriko', 'junker-queen', 'counter', 2],
        ['kiriko', 'roadhog', 'synergy', 2],
    ];

    public function up(): void
    {
        Schema::create('hero_matchups', function (Blueprint $table): void {
            $table->id();
            $table->string('hero_id', 50);
            $table->string('target_hero_id', 50);
            $table->enum('relation_type', ['counter', 'synergy']);
            $table->smallInteger('score');
            $table->unique(
                ['hero_id', 'target_hero_id', 'relation_type'],
                'hero_matchups_pair_relation_unique',
            );
            $table->index(
                ['target_hero_id', 'relation_type'],
                'hero_matchups_target_relation_index',
            );
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'ALTER TABLE hero_matchups ADD CONSTRAINT hero_matchups_score_range_check CHECK (score BETWEEN -2 AND 2)',
            );
        }

        $rows = array_map(
            static fn (array $matchup): array => [
                'hero_id' => $matchup[0],
                'target_hero_id' => $matchup[1],
                'relation_type' => $matchup[2],
                'score' => $matchup[3],
            ],
            self::MATCHUPS,
        );

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('hero_matchups')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_matchups');
    }
};
