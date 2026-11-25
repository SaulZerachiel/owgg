<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MAPS = [
        'dva' => [
            'favorable' => ['aatlis', 'shambali-monastery', 'paraiso'],
            'difficult' => ['grimsvotn', 'nepal', 'eichenwalde'],
        ],
        'dmon' => [
            'favorable' => ['aatlis', 'oasis', 'antarctic-peninsula'],
            'difficult' => ['rialto', 'kings-row', 'junkertown'],
        ],
        'domina' => [
            'favorable' => ['circuit-royal', 'blizzard-world', 'kings-row'],
            'difficult' => ['new-junk-city', 'suravasa', 'runasapi'],
        ],
        'doomfist' => [
            'favorable' => ['colosseo', 'neon-junction', 'dorado'],
            'difficult' => ['circuit-royal', 'midtown', 'havana'],
        ],
        'hazard' => [
            'favorable' => ['ilios', 'aatlis', 'nepal'],
            'difficult' => ['blizzard-world', 'circuit-royal', 'dorado'],
        ],
        'junker-queen' => [
            'favorable' => ['new-junk-city', 'oasis', 'runasapi'],
            'difficult' => ['watchpoint-gibraltar', 'eichenwalde', 'blizzard-world'],
        ],
        'mauga' => [
            'favorable' => ['runasapi', 'nepal', 'busan'],
            'difficult' => ['watchpoint-gibraltar', 'esperanca', 'kings-row'],
        ],
        'orisa' => [
            'favorable' => ['shambali-monastery', 'numbani', 'havana'],
            'difficult' => ['new-junk-city', 'suravasa', 'colosseo'],
        ],
        'ramattra' => [
            'favorable' => ['aatlis', 'antarctic-peninsula', 'hollywood'],
            'difficult' => ['esperanca', 'suravasa', 'colosseo'],
        ],
        'reinhardt' => [
            'favorable' => ['runasapi', 'grimsvotn', 'eichenwalde'],
            'difficult' => ['antarctic-peninsula', 'aatlis', 'new-junk-city'],
        ],
        'roadhog' => [
            'favorable' => ['ilios', 'new-junk-city', 'antarctic-peninsula'],
            'difficult' => ['colosseo', 'circuit-royal', 'esperanca'],
        ],
        'sigma' => [
            'favorable' => ['circuit-royal', 'blizzard-world', 'kings-row'],
            'difficult' => ['nepal', 'lijiang-tower', 'aatlis'],
        ],
        'winston' => [
            'favorable' => ['colosseo', 'esperanca', 'watchpoint-gibraltar'],
            'difficult' => ['havana', 'neon-junction', 'circuit-royal'],
        ],
        'wrecking-ball' => [
            'favorable' => ['hollywood', 'route-66', 'kings-row'],
            'difficult' => ['ilios', 'samoa', 'oasis'],
        ],
        'zarya' => [
            'favorable' => ['busan', 'antarctic-peninsula', 'samoa'],
            'difficult' => ['ilios', 'aatlis', 'colosseo'],
        ],
        'anran' => [
            'favorable' => ['esperanca', 'new-junk-city', 'new-queen-street'],
            'difficult' => ['watchpoint-gibraltar', 'numbani', 'paraiso'],
        ],
        'ashe' => [
            'favorable' => ['circuit-royal', 'numbani', 'hollywood'],
            'difficult' => ['antarctic-peninsula', 'ilios', 'lijiang-tower'],
        ],
        'bastion' => [
            'favorable' => ['circuit-royal', 'watchpoint-gibraltar', 'eichenwalde'],
            'difficult' => ['lijiang-tower', 'ilios', 'colosseo'],
        ],
        'cassidy' => [
            'favorable' => ['junkertown', 'oasis', 'runasapi'],
            'difficult' => ['circuit-royal', 'lijiang-tower', 'ilios'],
        ],
        'echo' => [
            'favorable' => ['samoa', 'junkertown', 'new-queen-street'],
            'difficult' => ['esperanca', 'runasapi', 'aatlis'],
        ],
        'emre' => [
            'favorable' => ['esperanca', 'aatlis', 'blizzard-world'],
            'difficult' => ['lijiang-tower', 'neon-junction', 'nepal'],
        ],
        'freja' => [
            'favorable' => ['numbani', 'ilios', 'grimsvotn'],
            'difficult' => ['rialto', 'neon-junction', 'lijiang-tower'],
        ],
        'genji' => [
            'favorable' => ['midtown', 'lijiang-tower', 'colosseo'],
            'difficult' => ['numbani', 'junkertown', 'runasapi'],
        ],
        'hanzo' => [
            'favorable' => ['paraiso', 'shambali-monastery', 'havana'],
            'difficult' => ['ilios', 'watchpoint-gibraltar', 'aatlis'],
        ],
        'junkrat' => [
            'favorable' => ['eichenwalde', 'rialto', 'grimsvotn'],
            'difficult' => ['esperanca', 'runasapi', 'new-queen-street'],
        ],
        'mei' => [
            'favorable' => ['circuit-royal', 'rialto', 'blizzard-world'],
            'difficult' => ['aatlis', 'runasapi', 'ilios'],
        ],
        'pharah' => [
            'favorable' => ['ilios', 'lijiang-tower', 'eichenwalde'],
            'difficult' => ['runasapi', 'midtown', 'havana'],
        ],
        'reaper' => [
            'favorable' => ['esperanca', 'neon-junction', 'new-queen-street'],
            'difficult' => ['aatlis', 'circuit-royal', 'shambali-monastery'],
        ],
        'shion' => [
            'favorable' => ['oasis', 'neon-junction', 'aatlis'],
            'difficult' => ['havana', 'kings-row', 'eichenwalde'],
        ],
        'sierra' => [
            'favorable' => ['kings-row', 'blizzard-world', 'circuit-royal'],
            'difficult' => ['new-junk-city', 'aatlis', 'ilios'],
        ],
        'sojourn' => [
            'favorable' => ['blizzard-world', 'runasapi', 'oasis'],
            'difficult' => ['colosseo', 'watchpoint-gibraltar', 'dorado'],
        ],
        'soldier-76' => [
            'favorable' => ['new-queen-street', 'runasapi', 'busan'],
            'difficult' => ['hollywood', 'kings-row', 'ilios'],
        ],
        'sombra' => [
            'favorable' => ['colosseo', 'esperanca', 'ilios'],
            'difficult' => ['antarctic-peninsula', 'havana', 'midtown'],
        ],
        'symmetra' => [
            'favorable' => ['lijiang-tower', 'nepal', 'suravasa'],
            'difficult' => ['havana', 'esperanca', 'colosseo'],
        ],
        'torbjorn' => [
            'favorable' => ['eichenwalde', 'blizzard-world', 'hollywood'],
            'difficult' => ['colosseo', 'esperanca', 'aatlis'],
        ],
        'tracer' => [
            'favorable' => ['aatlis', 'busan', 'colosseo'],
            'difficult' => ['dorado', 'numbani', 'blizzard-world'],
        ],
        'venture' => [
            'favorable' => ['grimsvotn', 'esperanca', 'paraiso'],
            'difficult' => ['junkertown', 'midtown', 'oasis'],
        ],
        'vendetta' => [
            'favorable' => ['grimsvotn', 'ilios', 'colosseo'],
            'difficult' => ['antarctic-peninsula', 'havana', 'circuit-royal'],
        ],
        'widowmaker' => [
            'favorable' => ['kings-row', 'havana', 'dorado'],
            'difficult' => ['new-queen-street', 'suravasa', 'oasis'],
        ],
        'ana' => [
            'favorable' => ['junkertown', 'kings-row', 'havana'],
            'difficult' => ['runasapi', 'ilios', 'suravasa'],
        ],
        'baptiste' => [
            'favorable' => ['suravasa', 'numbani', 'busan'],
            'difficult' => ['new-junk-city', 'grimsvotn', 'aatlis'],
        ],
        'brigitte' => [
            'favorable' => ['aatlis', 'new-junk-city', 'nepal'],
            'difficult' => ['kings-row', 'esperanca', 'circuit-royal'],
        ],
        'doctrine' => [
            'favorable' => ['shambali-monastery', 'junkertown', 'neon-junction'],
            'difficult' => ['new-junk-city', 'colosseo', 'ilios'],
        ],
        'illari' => [
            'favorable' => ['kings-row', 'neon-junction', 'ilios'],
            'difficult' => ['new-queen-street', 'new-junk-city', 'suravasa'],
        ],
        'juno' => [
            'favorable' => ['new-queen-street', 'colosseo', 'numbani'],
            'difficult' => ['grimsvotn', 'rialto', 'antarctic-peninsula'],
        ],
        'jetpack-cat' => [
            'favorable' => ['ilios', 'lijiang-tower', 'eichenwalde'],
            'difficult' => ['aatlis', 'midtown', 'neon-junction'],
        ],
        'kiriko' => [
            'favorable' => ['numbani', 'antarctic-peninsula', 'oasis'],
            'difficult' => ['circuit-royal', 'ilios', 'eichenwalde'],
        ],
        'lifeweaver' => [
            'favorable' => ['circuit-royal', 'dorado', 'watchpoint-gibraltar'],
            'difficult' => ['ilios', 'nepal', 'lijiang-tower'],
        ],
        'lucio' => [
            'favorable' => ['ilios', 'lijiang-tower', 'colosseo'],
            'difficult' => ['blizzard-world', 'watchpoint-gibraltar', 'kings-row'],
        ],
        'mercy' => [
            'favorable' => ['aatlis', 'route-66', 'havana'],
            'difficult' => ['new-junk-city', 'antarctic-peninsula', 'new-queen-street'],
        ],
        'mizuki' => [
            'favorable' => ['neon-junction', 'grimsvotn', 'rialto'],
            'difficult' => ['runasapi', 'colosseo', 'new-junk-city'],
        ],
        'moira' => [
            'favorable' => ['esperanca', 'suravasa', 'new-queen-street'],
            'difficult' => ['dorado', 'havana', 'numbani'],
        ],
        'wuyang' => [
            'favorable' => ['antarctic-peninsula', 'suravasa', 'esperanca'],
            'difficult' => ['paraiso', 'runasapi', 'lijiang-tower'],
        ],
        'zenyatta' => [
            'favorable' => ['numbani', 'havana', 'neon-junction'],
            'difficult' => ['nepal', 'aatlis', 'new-junk-city'],
        ],
    ];

    public function up(): void
    {
        Schema::create('counterwatch_hero_map_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->string('hero_id', 50);
            $table->string('map_id', 50);
            $table->enum('suitability', ['favorable', 'difficult']);
            $table->unsignedSmallInteger('priority');
            $table->string('source', 64);
            $table->string('source_url')->nullable();
            $table->unique(['hero_id', 'map_id'], 'cw_hero_maps_hero_map_unique');
            $table->unique(
                ['hero_id', 'suitability', 'priority'],
                'cw_hero_maps_hero_suitability_priority_unique',
            );
            $table->index(['hero_id', 'suitability'], 'cw_hero_maps_hero_suitability_index');
        });

        $rows = [];
        foreach (self::MAPS as $heroId => $recommendations) {
            foreach (['favorable', 'difficult'] as $suitability) {
                foreach ($recommendations[$suitability] as $index => $mapId) {
                    $rows[] = [
                        'hero_id' => $heroId,
                        'map_id' => $mapId,
                        'suitability' => $suitability,
                        'priority' => $index + 1,
                        'source' => 'counterwatch-map-check-2026-11-25',
                        'source_url' => null,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('counterwatch_hero_map_recommendations')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('counterwatch_hero_map_recommendations');
    }
};
