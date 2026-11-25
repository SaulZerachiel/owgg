<?php

namespace App\Services;

use App\Models\HeroMatchup;

final class CounterwatchGuide
{
    private const HERO_PROFILES = [
        'ana' => ['styles' => ['poke'], 'group' => 'backline'],
        'anran' => ['styles' => ['dive', 'brawl'], 'group' => 'flanker'],
        'ashe' => ['styles' => ['poke'], 'group' => 'sniper'],
        'baptiste' => ['styles' => ['poke'], 'group' => 'backline'],
        'bastion' => ['styles' => ['poke', 'brawl'], 'group' => 'frontline'],
        'brigitte' => ['styles' => ['brawl', 'dive'], 'group' => 'support'],
        'cassidy' => ['styles' => ['poke', 'brawl'], 'group' => 'sniper'],
        'dmon' => ['styles' => ['dive'], 'group' => 'dive'],
        'doomfist' => ['styles' => ['dive'], 'group' => 'dive'],
        'dva' => ['styles' => ['dive'], 'group' => 'dive'],
        'domina' => ['styles' => ['poke'], 'group' => 'poke'],
        'echo' => ['styles' => ['dive', 'poke'], 'group' => 'air'],
        'emre' => ['styles' => ['poke', 'dive'], 'group' => 'poke'],
        'freja' => ['styles' => ['poke', 'dive'], 'group' => 'poke'],
        'genji' => ['styles' => ['dive'], 'group' => 'flanker'],
        'hanzo' => ['styles' => ['poke'], 'group' => 'sniper'],
        'hazard' => ['styles' => ['dive', 'brawl'], 'group' => 'dive'],
        'illari' => ['styles' => ['poke'], 'group' => 'backline'],
        'jetpack-cat' => ['styles' => ['dive'], 'group' => 'air'],
        'junker-queen' => ['styles' => ['brawl'], 'group' => 'brawl'],
        'junkrat' => ['styles' => ['poke', 'brawl'], 'group' => 'frontline'],
        'juno' => ['styles' => ['dive', 'poke'], 'group' => 'support'],
        'kiriko' => ['styles' => ['dive', 'poke'], 'group' => 'support'],
        'lifeweaver' => ['styles' => ['poke'], 'group' => 'backline'],
        'lucio' => ['styles' => ['brawl', 'dive'], 'group' => 'support'],
        'mauga' => ['styles' => ['brawl', 'poke'], 'group' => 'brawl'],
        'mei' => ['styles' => ['brawl', 'poke'], 'group' => 'frontline'],
        'mercy' => ['styles' => ['poke', 'dive'], 'group' => 'support'],
        'mizuki' => ['styles' => ['brawl', 'dive'], 'group' => 'support'],
        'moira' => ['styles' => ['brawl', 'dive'], 'group' => 'support'],
        'orisa' => ['styles' => ['brawl', 'poke'], 'group' => 'brawl'],
        'pharah' => ['styles' => ['poke'], 'group' => 'air'],
        'ramattra' => ['styles' => ['brawl', 'poke'], 'group' => 'brawl'],
        'reaper' => ['styles' => ['brawl', 'dive'], 'group' => 'flanker'],
        'reinhardt' => ['styles' => ['brawl'], 'group' => 'brawl'],
        'roadhog' => ['styles' => ['brawl'], 'group' => 'brawl'],
        'shion' => ['styles' => ['dive'], 'group' => 'flanker'],
        'sierra' => ['styles' => ['poke', 'dive'], 'group' => 'poke'],
        'sigma' => ['styles' => ['poke'], 'group' => 'poke'],
        'sojourn' => ['styles' => ['poke', 'dive'], 'group' => 'sniper'],
        'soldier-76' => ['styles' => ['poke'], 'group' => 'poke'],
        'sombra' => ['styles' => ['dive'], 'group' => 'flanker'],
        'symmetra' => ['styles' => ['brawl', 'poke'], 'group' => 'deployable'],
        'torbjorn' => ['styles' => ['poke', 'brawl'], 'group' => 'deployable'],
        'tracer' => ['styles' => ['dive'], 'group' => 'flanker'],
        'vendetta' => ['styles' => ['dive', 'brawl'], 'group' => 'flanker'],
        'venture' => ['styles' => ['dive', 'brawl'], 'group' => 'flanker'],
        'widowmaker' => ['styles' => ['poke'], 'group' => 'sniper'],
        'winston' => ['styles' => ['dive'], 'group' => 'dive'],
        'wrecking-ball' => ['styles' => ['dive'], 'group' => 'dive'],
        'wuyang' => ['styles' => ['poke', 'dive'], 'group' => 'support'],
        'zenyatta' => ['styles' => ['poke'], 'group' => 'backline'],
    ];

    private const GROUP_NOTES = [
        'air' => [
            'strengths' => ['Menace les cibles au sol depuis des angles verticaux.', 'Peut contourner une partie des lignes de front et forcer l’adversaire à lever les yeux.'],
            'weaknesses' => ['Les tirs précis à distance et les héros capables de contester les airs réduisent ses options.', 'Les couloirs fermés et le manque d’espace limitent ses angles.'],
        ],
        'backline' => [
            'strengths' => ['Apporte soin ou utilité à distance sans devoir suivre le tank au contact.', 'Peut peser sur le rythme du combat avec ses capacités plutôt qu’avec les seuls dégâts.'],
            'weaknesses' => ['Une Dive coordonnée ou des angles multiples peuvent lui couper ses lignes de vue.', 'Sa valeur dépend souvent du placement et du soutien de son équipe.'],
        ],
        'brawl' => [
            'strengths' => ['Excelle quand l’équipe avance groupée et peut maintenir la pression à courte portée.', 'Les espaces resserrés et les objectifs rapprochés favorisent son impact.'],
            'weaknesses' => ['Doit franchir les lignes de tir et peut perdre de la valeur si l’ennemi garde ses distances.', 'Les contrôles et les dégâts concentrés punissent un engagement mal synchronisé.'],
        ],
        'deployable' => [
            'strengths' => ['Peut verrouiller une zone et punir les adversaires qui répètent la même approche.', 'Les installations et la pression de zone compliquent les rotations prévisibles.'],
            'weaknesses' => ['Les angles éloignés et les engagements mobiles peuvent contourner sa zone de contrôle.', 'Les éléments statiques perdent de la valeur s’ils sont détruits ou ignorés.'],
        ],
        'dive' => [
            'strengths' => ['Crée une entrée rapide sur une cible isolée et peut repartir avant la riposte.', 'Les hauteurs, les accès latéraux et les cibles sans échappatoire ouvrent ses meilleures fenêtres.'],
            'weaknesses' => ['Les contrôles, le peel groupé et les capacités défensives synchronisées cassent son engagement.', 'Une entrée sans alliés ni sortie disponible peut se transformer en élimination facile.'],
        ],
        'flanker' => [
            'strengths' => ['Menace les soutiens et les tireurs depuis des angles inattendus.', 'Peut forcer l’adversaire à détourner son attention et ses ressources.'],
            'weaknesses' => ['Le peel, les contrôles et le jeu en groupe réduisent ses duels isolés.', 'Doit gérer ses temps de recharge pour engager puis s’extraire.'],
        ],
        'frontline' => [
            'strengths' => ['Peut tenir un espace ou gêner une avancée grâce à une pression régulière.', 'Les adversaires regroupés ou contraints de passer par un passage étroit lui donnent des cibles prévisibles.'],
            'weaknesses' => ['Les angles croisés et les déplacements rapides compliquent sa pression frontale.', 'S’il dépense ses outils défensifs trop tôt, l’équipe peut le contourner ou le punir.'],
        ],
        'poke' => [
            'strengths' => ['Use la ligne adverse à distance et profite des longues lignes de vue.', 'Peut forcer l’ennemi à dépenser des ressources avant même le combat rapproché.'],
            'weaknesses' => ['Les couvertures et les rotations rapides réduisent le temps passé sous pression.', 'Une Dive coordonnée peut atteindre ses positions avant qu’il ne reprenne ses distances.'],
        ],
        'sniper' => [
            'strengths' => ['Punit les traversées prévisibles et crée une menace de tir qui modifie les déplacements adverses.', 'Les longues lignes de vue et les hauteurs augmentent sa portée d’influence.'],
            'weaknesses' => ['Les couvertures, les boucliers et les engagements par plusieurs angles limitent ses tirs.', 'Une Dive qui l’atteint l’oblige à choisir entre fuir et continuer à viser.'],
        ],
        'support' => [
            'strengths' => ['Peut sauver un allié, retourner un duel ou accélérer une rotation selon son kit.', 'Sa mobilité ou ses outils de survie lui permettent parfois de tenir une position risquée.'],
            'weaknesses' => ['La pression sur la ligne arrière peut détourner ses ressources du reste de l’équipe.', 'Les temps de recharge défensifs sont une fenêtre à exploiter après leur utilisation.'],
        ],
    ];

    private const HERO_NOTES = [
        'ana' => [
            'strengths' => ['Soin à longue portée et utilitaires capables de retourner un combat.', 'La grenade peut punir un regroupement ou soutenir une fenêtre d’élimination.'],
            'weaknesses' => ['Peu d’outils de déplacement : une Dive coordonnée peut la forcer à utiliser Sommeil en défense.', 'Ses lignes de vue et ses temps de recharge comptent davantage que son seul choix de composition.'],
        ],
        'mei' => [
            'strengths' => ['Le Mur peut couper une ligne de vue, isoler une cible ou protéger une capacité canalisée.', 'Ses ralentissements et son gel de zone compliquent les engagements prévisibles.'],
            'weaknesses' => ['Sa portée et sa mobilité limitent son influence quand l’équipe adverse joue très écartée.', 'Un mur mal placé peut aussi couper les lignes de vue de ses propres alliés.'],
        ],
        'pharah' => [
            'strengths' => ['Les angles aériens et les dégâts de zone punissent les équipes regroupées ou peu mobiles.', 'Peut créer une menace depuis des positions que les adversaires au sol contestent difficilement.'],
            'weaknesses' => ['Les tirs précis à distance et la pression de D.Va sont des réponses fréquentes.', 'Elle n’est pas automatiquement forte contre une composition Dive : les héros mobiles peuvent la contester ou atteindre son équipe.'],
        ],
    ];

    private const MAP_GUIDES = [
        'sightline' => [
            'favorable' => [
                ['circuit-royal', 'Les longues avenues, surtout au premier segment, offrent des lignes de tir dégagées aux héros qui veulent garder leurs distances.'],
                ['havana', 'Les approches ouvertes et les longues rues récompensent la portée; les zones urbaines raccourcissent toutefois certains duels.'],
                ['junkertown', 'Les premiers segments exposent le convoi sur de longues lignes et donnent plusieurs positions en hauteur.'],
            ],
            'difficult' => [
                ['kings-row', 'Les rues étroites et les angles cassés raccourcissent souvent les lignes de tir.'],
                ['lijiang-tower', 'Control Center et Night Market sont resserrés; Garden est plus ouvert, donc l’avantage dépend du sous-plan.'],
                ['samoa', 'Plusieurs zones de combat sont entourées de bâtiments et de couvertures qui coupent les longues lignes de vue.'],
            ],
        ],
        'close' => [
            'favorable' => [
                ['kings-row', 'Les rues étroites et les nombreux coins permettent d’approcher sous couvert et de forcer des combats rapprochés.'],
                ['lijiang-tower', 'Control Center et Night Market favorisent les engagements autour d’objectifs compacts; Garden est plus ouvert.'],
                ['samoa', 'Downtown et Volcano offrent des zones resserrées où une équipe groupée peut tenir l’espace; Beach est plus ouvert.'],
            ],
            'difficult' => [
                ['circuit-royal', 'Les longues traversées exposées donnent à l’adversaire le temps de faire des dégâts avant l’engagement.'],
                ['havana', 'Les lignes de tir longues et les segments ouverts rendent l’approche au contact plus coûteuse.'],
                ['junkertown', 'Les premiers segments offrent de longues lignes et des hauteurs qui peuvent retarder une entrée groupée.'],
            ],
        ],
        'vertical' => [
            'favorable' => [
                ['watchpoint-gibraltar', 'Les hauteurs successives, les passerelles et les accès latéraux créent plusieurs routes d’engagement et de sortie.'],
                ['dorado', 'Les toits et les ruelles latérales permettent de changer d’angle et d’atteindre les positions arrière.'],
                ['numbani', 'Les différences de hauteur et les accès parallèles facilitent les attaques depuis plusieurs directions.'],
            ],
            'difficult' => [
                ['circuit-royal', 'Certaines traversées sont très exposées et offrent peu d’approches sûres avant d’atteindre les positions en hauteur.'],
                ['havana', 'Les longues lignes dégagées peuvent révéler une approche avant que les héros mobiles aient atteint leur cible.'],
                ['esperanca', 'Les grandes places et les longues rues exposent les engagements si l’équipe ne coordonne pas ses couvertures.'],
            ],
        ],
    ];

    /**
     * @param  array<string, mixed>  $hero
     * @param  list<array<string, mixed>>  $roster
     * @param  list<array<string, mixed>>  $maps
     * @param  list<array<string, mixed>>  $matchups
     * @param  array<string, array{winrate: float, pickrate: float|null}>  $heroStats
     * @return array<string, mixed>
     */
    public function heroGuide(
        array $hero,
        array $roster,
        array $maps = [],
        array $matchups = [],
        array $heroStats = [],
    ): array {
        $key = is_string($hero['key'] ?? null) ? $hero['key'] : '';
        $role = is_string($hero['role'] ?? null) ? $hero['role'] : '';
        $subrole = is_string($hero['subrole'] ?? null) ? $hero['subrole'] : '';
        $profile = self::HERO_PROFILES[$key] ?? $this->fallbackProfile($role, $subrole);
        $notes = self::HERO_NOTES[$key] ?? self::GROUP_NOTES[$profile['group']];
        $rosterByKey = [];

        foreach ($roster as $candidate) {
            if (is_string($candidate['key'] ?? null) && is_string($candidate['name'] ?? null)) {
                $rosterByKey[$candidate['key']] = $candidate;
            }
        }

        $counters = [
            'tank' => [],
            'damage' => [],
            'support' => [],
        ];
        foreach (['tank', 'damage', 'support'] as $counterRole) {
            $counters[$counterRole] = [];

            $candidateScores = [];
            foreach ($matchups as $matchup) {
                if (($matchup['relation_type'] ?? null) !== 'counter'
                    || ! is_string($matchup['hero_id'] ?? null)
                    || ! is_string($matchup['target_hero_id'] ?? null)
                    || ! is_numeric($matchup['score'] ?? null)) {
                    continue;
                }

                $score = (int) $matchup['score'];
                $candidateKey = null;
                if ($matchup['target_hero_id'] === $key && $score > 0) {
                    $candidateKey = $matchup['hero_id'];
                } elseif ($matchup['hero_id'] === $key && $score < 0) {
                    $candidateKey = $matchup['target_hero_id'];
                    $score = abs($score);
                }

                if ($candidateKey === null
                    || $candidateKey === $key
                    || ! isset($rosterByKey[$candidateKey])) {
                    continue;
                }

                $candidate = $rosterByKey[$candidateKey];
                if (($candidate['role'] ?? null) !== $counterRole) {
                    continue;
                }

                $candidateScores[$candidateKey] = max(
                    $candidateScores[$candidateKey] ?? 0,
                    $score,
                );
            }

            foreach ($candidateScores as $candidateKey => $score) {
                $candidate = $rosterByKey[$candidateKey];
                $counters[$counterRole][] = [
                    'key' => $candidateKey,
                    'name' => $candidate['name'],
                    'score' => $score,
                    'winrate' => $heroStats[$candidateKey]['winrate'] ?? null,
                    'winrateDifference' => isset($heroStats[$candidateKey]['winrate'], $heroStats[$key]['winrate'])
                        ? round($heroStats[$candidateKey]['winrate'] - $heroStats[$key]['winrate'], 1)
                        : null,
                ];
            }

            usort($counters[$counterRole], static function (array $first, array $second): int {
                if ($first['score'] !== $second['score']) {
                    return $second['score'] <=> $first['score'];
                }

                if ($first['winrate'] === $second['winrate']) {
                    return strcmp($first['name'], $second['name']);
                }

                if ($first['winrate'] === null) {
                    return 1;
                }

                if ($second['winrate'] === null) {
                    return -1;
                }

                return $second['winrate'] <=> $first['winrate'];
            });
            $counters[$counterRole] = array_slice($counters[$counterRole], 0, 3);
        }

        return [
            'styles' => array_map(fn (string $style): array => [
                'key' => $style,
                'label' => $this->styleLabel($style),
            ], $profile['styles']),
            'strengths' => $notes['strengths'],
            'weaknesses' => $notes['weaknesses'],
            'counters' => $counters,
            'maps' => $this->mapGuide($profile, $maps),
            'confidence' => isset(self::HERO_PROFILES[$key]) ? 'curated' : 'inferred',
        ];
    }

    /**
     * @return list<array{hero_id: string, target_hero_id: string, relation_type: string, score: int}>
     */
    public function matchups(): array
    {
        $records = HeroMatchup::query()
            ->orderBy('target_hero_id')
            ->orderBy('relation_type')
            ->orderBy('hero_id')
            ->get([
                'hero_id',
                'target_hero_id',
                'relation_type',
                'score',
            ]);
        $matchups = [];

        foreach ($records as $record) {
            $matchups[] = [
                'hero_id' => (string) $record->hero_id,
                'target_hero_id' => (string) $record->target_hero_id,
                'relation_type' => (string) $record->relation_type,
                'score' => (int) $record->score,
            ];
        }

        return $matchups;
    }

    /**
     * @return array<string, mixed>
     */
    public function compositionGuide(): array
    {
        return [
            'cycle' => [
                ['key' => 'poke', 'label' => 'Poke', 'beats' => 'brawl', 'description' => 'Use la composition Brawl à distance avant qu’elle ne puisse engager.'],
                ['key' => 'brawl', 'label' => 'Brawl', 'beats' => 'dive', 'description' => 'Le groupe compact protège sa ligne arrière et punit les héros Dive au contact.'],
                ['key' => 'dive', 'label' => 'Dive', 'beats' => 'poke', 'description' => 'La mobilité ferme la distance et conteste les positions de tir isolées.'],
            ],
            'caveat' => 'Ce cycle décrit des tendances, pas une règle garantie. La carte, les perks, les temps de recharge, les angles et la coordination peuvent renverser un matchup.',
        ];
    }

    private function styleLabel(string $style): string
    {
        return match ($style) {
            'brawl' => 'Brawl',
            'dive' => 'Dive',
            'poke' => 'Poke',
            default => 'Hybride',
        };
    }

    /**
     * @param  array{styles: list<string>, group: string}  $profile
     * @param  list<array<string, mixed>>  $maps
     * @return array{favorable: list<array{key: string, name: string, reason: string}>, difficult: list<array{key: string, name: string, reason: string}>}
     */
    private function mapGuide(array $profile, array $maps): array
    {
        $family = match ($profile['group']) {
            'air', 'backline', 'poke', 'sniper' => 'sightline',
            'brawl', 'deployable', 'frontline' => 'close',
            'support' => in_array('brawl', $profile['styles'], true) ? 'close' : 'vertical',
            default => 'vertical',
        };
        $mapsByKey = [];

        foreach ($maps as $map) {
            if (is_string($map['key'] ?? null)
                && is_string($map['name'] ?? null)
                && is_array($map['uses'] ?? null)
                && in_array('standard', $map['uses'], true)) {
                $mapsByKey[$map['key']] = $map;
            }
        }

        $recommendations = [];
        foreach (['favorable', 'difficult'] as $rating) {
            $recommendations[$rating] = [];

            foreach (self::MAP_GUIDES[$family][$rating] as [$mapKey, $reason]) {
                if (! isset($mapsByKey[$mapKey])) {
                    continue;
                }

                $recommendations[$rating][] = [
                    'key' => $mapKey,
                    'name' => $mapsByKey[$mapKey]['name'],
                    'reason' => $reason,
                ];
            }
        }

        return $recommendations;
    }

    /**
     * @return array{styles: list<string>, group: string}
     */
    private function fallbackProfile(string $role, string $subrole): array
    {
        return match ([$role, $subrole]) {
            ['tank', 'initiator'] => ['styles' => ['dive'], 'group' => 'dive'],
            ['tank', 'bruiser'] => ['styles' => ['brawl'], 'group' => 'brawl'],
            ['tank', 'stalwart'] => ['styles' => ['brawl', 'poke'], 'group' => 'poke'],
            ['damage', 'flanker'] => ['styles' => ['dive'], 'group' => 'flanker'],
            ['damage', 'sharpshooter'] => ['styles' => ['poke'], 'group' => 'sniper'],
            ['damage', 'recon'] => ['styles' => ['poke', 'dive'], 'group' => 'poke'],
            ['damage', 'specialist'] => ['styles' => ['brawl', 'poke'], 'group' => 'frontline'],
            ['support', 'tactician'] => ['styles' => ['poke', 'brawl'], 'group' => 'backline'],
            ['support', 'survivor'] => ['styles' => ['brawl', 'dive'], 'group' => 'support'],
            ['support', 'medic'] => ['styles' => ['poke', 'dive'], 'group' => 'support'],
            default => ['styles' => [], 'group' => 'support'],
        };
    }
}
