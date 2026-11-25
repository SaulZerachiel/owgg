<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Counterwatch's three overall hardest counters per hero, ordered for OWGG's internal ranking.
    private const COUNTERS = [
        'dmon' => ['ramattra', 'pharah', 'torbjorn'],
        'dva' => ['brigitte', 'dmon', 'reinhardt'],
        'domina' => ['symmetra', 'ana', 'lifeweaver'],
        'doomfist' => ['mauga', 'roadhog', 'pharah'],
        'hazard' => ['mauga', 'sigma', 'orisa'],
        'junker-queen' => ['sombra', 'sierra', 'moira'],
        'mauga' => ['illari', 'symmetra', 'zenyatta'],
        'orisa' => ['symmetra', 'mauga', 'torbjorn'],
        'ramattra' => ['zenyatta', 'doctrine', 'mauga'],
        'reinhardt' => ['pharah', 'torbjorn', 'zenyatta'],
        'roadhog' => ['echo', 'baptiste', 'doctrine'],
        'sigma' => ['symmetra', 'echo', 'doctrine'],
        'winston' => ['roadhog', 'mauga', 'bastion'],
        'wrecking-ball' => ['mauga', 'roadhog', 'domina'],
        'zarya' => ['symmetra', 'baptiste', 'brigitte'],
        'anran' => ['winston', 'moira', 'wrecking-ball'],
        'ashe' => ['widowmaker', 'winston', 'lifeweaver'],
        'bastion' => ['doctrine', 'genji', 'baptiste'],
        'cassidy' => ['widowmaker', 'wuyang', 'hanzo'],
        'echo' => ['illari', 'baptiste', 'juno'],
        'emre' => ['lifeweaver', 'zarya', 'mercy'],
        'freja' => ['dva', 'ashe', 'soldier-76'],
        'genji' => ['wrecking-ball', 'winston', 'moira'],
        'hanzo' => ['zarya', 'sigma', 'doomfist'],
        'junkrat' => ['pharah', 'widowmaker', 'ashe'],
        'mei' => ['jetpack-cat', 'pharah', 'juno'],
        'pharah' => ['juno', 'ashe', 'jetpack-cat'],
        'reaper' => ['brigitte', 'sojourn', 'widowmaker'],
        'shion' => ['anran', 'vendetta', 'venture'],
        'sierra' => ['moira', 'genji', 'winston'],
        'sojourn' => ['winston', 'genji', 'wrecking-ball'],
        'soldier-76' => ['dmon', 'doomfist', 'vendetta'],
        'symmetra' => ['widowmaker', 'ashe', 'wuyang'],
        'torbjorn' => ['lifeweaver', 'mercy', 'juno'],
        'tracer' => ['cassidy', 'junkrat', 'hazard'],
        'vendetta' => ['pharah', 'junkrat', 'wrecking-ball'],
        'venture' => ['pharah', 'doomfist', 'winston'],
        'widowmaker' => ['winston', 'wrecking-ball', 'doomfist'],
        'ana' => ['anran', 'winston', 'venture'],
        'baptiste' => ['lucio', 'kiriko', 'symmetra'],
        'brigitte' => ['pharah', 'ramattra', 'junkrat'],
        'doctrine' => ['tracer', 'dmon', 'winston'],
        'illari' => ['anran', 'winston', 'moira'],
        'jetpack-cat' => ['ashe', 'cassidy', 'soldier-76'],
        'juno' => ['soldier-76', 'ashe', 'tracer'],
        'kiriko' => ['vendetta', 'genji', 'venture'],
        'lifeweaver' => ['wrecking-ball', 'doomfist', 'winston'],
        'lucio' => ['hazard', 'pharah', 'winston'],
        'mercy' => ['wrecking-ball', 'dmon', 'hazard'],
        'mizuki' => ['genji', 'hanzo', 'jetpack-cat'],
        'moira' => ['wrecking-ball', 'roadhog', 'doomfist'],
        'sombra' => ['tracer', 'venture', 'kiriko'],
        'wuyang' => ['wrecking-ball', 'jetpack-cat', 'winston'],
        'zenyatta' => ['mercy', 'jetpack-cat', 'lucio'],
    ];

    private const PREVIOUS_COUNTERS = [
        ['winston', 'bastion', -2],
        ['winston', 'reaper', -2],
        ['winston', 'dva', -1],
        ['winston', 'mauga', -2],
        ['winston', 'widowmaker', 2],
        ['winston', 'genji', 2],
        ['winston', 'zenyatta', 2],
        ['winston', 'ana', 1],
        ['bastion', 'winston', 2],
        ['bastion', 'reinhardt', 2],
        ['bastion', 'zarya', 1],
        ['bastion', 'genji', -2],
        ['bastion', 'ana', -2],
        ['bastion', 'sigma', -2],
        ['reinhardt', 'bastion', -2],
        ['reinhardt', 'pharah', -2],
        ['reinhardt', 'orisa', -1],
        ['reinhardt', 'zarya', 1],
        ['reinhardt', 'soldier-76', 1],
        ['dva', 'zarya', -2],
        ['dva', 'symmetra', -2],
        ['dva', 'pharah', 2],
        ['dva', 'bastion', 1],
        ['dva', 'widowmaker', 2],
        ['dva', 'winston', 1],
        ['zarya', 'dva', 2],
        ['zarya', 'genji', 2],
        ['zarya', 'orisa', 1],
        ['zarya', 'bastion', -1],
        ['zarya', 'reinhardt', -1],
        ['zarya', 'pharah', -2],
        ['pharah', 'soldier-76', -2],
        ['pharah', 'cassidy', -2],
        ['pharah', 'ashe', -2],
        ['pharah', 'widowmaker', -2],
        ['pharah', 'reinhardt', 2],
        ['pharah', 'junkrat', 2],
        ['genji', 'bastion', 2],
        ['genji', 'widowmaker', 2],
        ['genji', 'winston', -2],
        ['genji', 'zarya', -2],
        ['genji', 'moira', -2],
        ['ana', 'roadhog', 2],
        ['ana', 'mauga', 2],
        ['ana', 'bastion', 2],
        ['ana', 'kiriko', -2],
        ['ana', 'winston', -1],
        ['ana', 'tracer', -2],
        ['kiriko', 'ana', 2],
        ['kiriko', 'junker-queen', 2],
    ];

    public function up(): void
    {
        DB::table('hero_matchups')
            ->where('relation_type', 'counter')
            ->delete();

        $positiveCounters = [];
        foreach (self::COUNTERS as $targetHeroId => $counterHeroIds) {
            foreach ($counterHeroIds as $index => $counterHeroId) {
                $positiveCounters[$counterHeroId.'|'.$targetHeroId] = [
                    'hero_id' => $counterHeroId,
                    'target_hero_id' => $targetHeroId,
                    'relation_type' => 'counter',
                    'score' => $index === 0 ? 2 : 1,
                ];
            }
        }

        foreach (array_chunk(array_values($positiveCounters), 50) as $chunk) {
            DB::table('hero_matchups')->insert($chunk);
        }

        $negativeCounters = [];
        foreach ($positiveCounters as $counter) {
            $reversePair = $counter['target_hero_id'].'|'.$counter['hero_id'];
            if (isset($positiveCounters[$reversePair])) {
                continue;
            }

            $negativeCounters[] = [
                'hero_id' => $counter['target_hero_id'],
                'target_hero_id' => $counter['hero_id'],
                'relation_type' => 'counter',
                'score' => -$counter['score'],
            ];
        }

        foreach (array_chunk($negativeCounters, 50) as $chunk) {
            DB::table('hero_matchups')->insert($chunk);
        }
    }

    public function down(): void
    {
        DB::table('hero_matchups')
            ->where('relation_type', 'counter')
            ->delete();

        $rows = array_map(
            static fn (array $counter): array => [
                'hero_id' => $counter[0],
                'target_hero_id' => $counter[1],
                'relation_type' => 'counter',
                'score' => $counter[2],
            ],
            self::PREVIOUS_COUNTERS,
        );

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('hero_matchups')->insert($chunk);
        }
    }
};
