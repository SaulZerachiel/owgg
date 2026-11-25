<?php

namespace App\Services;

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
            'weaknesses' => ['Une plongée coordonnée ou des angles multiples peuvent lui couper ses lignes de vue.', 'Sa valeur dépend souvent du placement et du soutien de son équipe.'],
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
            'weaknesses' => ['Les couvertures et les rotations rapides réduisent le temps passé sous pression.', 'Une plongée coordonnée peut atteindre ses positions avant qu’il ne reprenne ses distances.'],
        ],
        'sniper' => [
            'strengths' => ['Punit les traversées prévisibles et crée une menace de tir qui modifie les déplacements adverses.', 'Les longues lignes de vue et les hauteurs augmentent sa portée d’influence.'],
            'weaknesses' => ['Les couvertures, les boucliers et les engagements par plusieurs angles limitent ses tirs.', 'Une plongée qui l’atteint l’oblige à choisir entre fuir et continuer à viser.'],
        ],
        'support' => [
            'strengths' => ['Peut sauver un allié, retourner un duel ou accélérer une rotation selon son kit.', 'Sa mobilité ou ses outils de survie lui permettent parfois de tenir une position risquée.'],
            'weaknesses' => ['La pression sur la ligne arrière peut détourner ses ressources du reste de l’équipe.', 'Les temps de recharge défensifs sont une fenêtre à exploiter après leur utilisation.'],
        ],
    ];

    private const HERO_NOTES = [
        'ana' => [
            'strengths' => ['Soin à longue portée et utilitaires capables de retourner un combat.', 'La grenade peut punir un regroupement ou soutenir une fenêtre d’élimination.'],
            'weaknesses' => ['Peu d’outils de déplacement : une plongée coordonnée peut la forcer à utiliser Sommeil en défense.', 'Ses lignes de vue et ses temps de recharge comptent davantage que son seul choix de composition.'],
        ],
        'mei' => [
            'strengths' => ['Le Mur peut couper une ligne de vue, isoler une cible ou protéger une capacité canalisée.', 'Ses ralentissements et son gel de zone compliquent les engagements prévisibles.'],
            'weaknesses' => ['Sa portée et sa mobilité limitent son influence quand l’équipe adverse joue très écartée.', 'Un mur mal placé peut aussi couper les lignes de vue de ses propres alliés.'],
        ],
        'pharah' => [
            'strengths' => ['Les angles aériens et les dégâts de zone punissent les équipes regroupées ou peu mobiles.', 'Peut créer une menace depuis des positions que les adversaires au sol contestent difficilement.'],
            'weaknesses' => ['Les tirs précis à distance et la pression de D.Va sont des réponses fréquentes.', 'Elle n’est pas automatiquement forte contre une composition Dive : les plongeurs mobiles peuvent la contester ou atteindre son équipe.'],
        ],
    ];

    private const COUNTERS = [
        'air' => [
            'tank' => [
                ['dva', 'Peut contester les airs avec sa mobilité et intercepter des projectiles avec Matrice défensive.'],
                ['winston', 'Peut protéger une ligne arrière sous pression ou rejoindre une cible qui atterrit.'],
                ['sigma', 'Peut contester des angles à distance et protéger une ligne de tir avec sa barrière.'],
            ],
            'damage' => [
                ['cassidy', 'Ses tirs précis menacent les trajectoires prévisibles à portée moyenne.'],
                ['ashe', 'Peut contester les airs depuis une ligne de vue dégagée et garder ses distances.'],
                ['soldier-76', 'Sa pression continue aide à forcer une cible aérienne à se mettre à couvert.'],
            ],
            'support' => [
                ['ana', 'Peut contribuer à distance si elle garde une ligne de vue sûre.'],
                ['illari', 'Peut ajouter une pression précise à distance sans quitter sa position.'],
                ['baptiste', 'Ses tirs peuvent contribuer contre une cible aérienne depuis une bonne ligne de vue.'],
            ],
        ],
        'backline' => [
            'tank' => [
                ['winston', 'Sa mobilité lui permet de menacer la ligne arrière et de couper des lignes de vue.'],
                ['dva', 'Peut engager rapidement une cible isolée et couvrir la retraite de son équipe.'],
                ['doomfist', 'Peut créer une interruption et déplacer une cible hors de sa position confortable.'],
            ],
            'damage' => [
                ['tracer', 'Peut arriver par un angle latéral et forcer la cible à détourner son attention.'],
                ['venture', 'Peut arriver par un angle inattendu et menacer une cible isolée à courte portée.'],
                ['genji', 'Peut exploiter les hauteurs et suivre une cible qui tente de s’échapper.'],
            ],
            'support' => [
                ['brigitte', 'Peut protéger une ligne arrière menacée et rendre l’approche directe plus coûteuse.'],
                ['kiriko', 'Peut rejoindre un allié sous pression et aider à survivre à une fenêtre de burst.'],
                ['lucio', 'Peut accélérer une rotation groupée pour fermer la distance ou sortir d’une ligne de tir.'],
            ],
        ],
        'brawl' => [
            'tank' => [
                ['sigma', 'Peut contrôler une ligne de tir et forcer un adversaire de courte portée à avancer sous pression.'],
                ['orisa', 'Peut contester l’espace frontal et ralentir une avancée directe.'],
                ['ramattra', 'Peut alterner pression à distance et présence au contact selon la fenêtre d’engagement.'],
            ],
            'damage' => [
                ['pharah', 'Peut attaquer depuis un angle vertical et éviter une partie de la pression au sol.'],
                ['bastion', 'Peut opposer une forte pression frontale si l’équipe lui crée une ligne de tir sûre.'],
                ['echo', 'Peut exploiter les angles verticaux et punir une cible déjà affaiblie.'],
            ],
            'support' => [
                ['ana', 'Sa grenade peut gêner un engagement groupé si elle est lancée au bon moment.'],
                ['illari', 'Peut maintenir une pression à distance tout en soignant depuis un angle sûr.'],
                ['lifeweaver', 'Peut déplacer un allié hors d’un engagement ou casser une ligne de front.'],
            ],
        ],
        'deployable' => [
            'tank' => [
                ['winston', 'Peut franchir la zone contrôlée et forcer les défenseurs à se retourner.'],
                ['dva', 'Peut traverser rapidement un angle ou une position statique et perturber son installation.'],
                ['doomfist', 'Peut atteindre les positions arrière et déplacer les défenseurs hors de leur zone.'],
            ],
            'damage' => [
                ['pharah', 'Peut attaquer la zone depuis les airs sans emprunter les accès au sol habituels.'],
                ['junkrat', 'Peut déloger une position fixe avec des dégâts de zone et des rebonds.'],
                ['echo', 'Peut contourner une installation statique avec ses angles verticaux.'],
            ],
            'support' => [
                ['lucio', 'Peut accélérer l’équipe pour traverser la zone avant qu’elle ne soit verrouillée.'],
                ['kiriko', 'Peut aider un allié à rejoindre une position difficile ou à sortir d’un contrôle.'],
                ['moira', 'Peut traverser une zone dangereuse et continuer à soutenir une entrée rapprochée.'],
            ],
        ],
        'dive' => [
            'tank' => [
                ['orisa', 'Peut tenir sa position et gêner une entrée directe avec ses outils de contrôle.'],
                ['junker-queen', 'Peut soutenir un combat rapproché groupé et punir une cible qui reste à portée.'],
                ['reinhardt', 'Peut protéger ses alliés si l’équipe reste compacte et joue autour de son bouclier.'],
            ],
            'damage' => [
                ['mei', 'Peut ralentir une entrée et placer un mur pour isoler ou couper la retraite.'],
                ['cassidy', 'Peut menacer les engagements qui arrivent dans sa portée et protéger un allié proche.'],
                ['torbjorn', 'Peut ajouter une menace de zone et compliquer les approches répétées.'],
            ],
            'support' => [
                ['brigitte', 'Peut protéger la ligne arrière et rendre les engagements rapprochés moins sûrs.'],
                ['moira', 'Peut survivre à une pression rapprochée et continuer à soutenir son équipe.'],
                ['illari', 'Peut soutenir une ligne arrière qui joue groupée et punir une entrée visible.'],
            ],
        ],
        'flanker' => [
            'tank' => [
                ['dva', 'Peut protéger un allié ciblé et suivre rapidement le flanqueur.'],
                ['orisa', 'Peut gêner une approche frontale et punir un adversaire qui s’expose.'],
                ['junker-queen', 'Peut soutenir un duel rapproché et aider à garder l’équipe groupée.'],
            ],
            'damage' => [
                ['mei', 'Peut ralentir une approche et couper une route avec son mur.'],
                ['torbjorn', 'Sa tourelle ajoute une alerte et une pression sur les angles répétés.'],
                ['cassidy', 'Peut tenir une ligne proche et menacer un flanqueur qui s’engage sans ressources.'],
            ],
            'support' => [
                ['brigitte', 'Peut rester près de la cible menacée et décourager une approche directe.'],
                ['moira', 'Peut échapper à une partie de la pression et riposter à courte portée.'],
                ['kiriko', 'Peut rejoindre un allié isolé et aider à traverser une fenêtre de burst.'],
            ],
        ],
        'frontline' => [
            'tank' => [
                ['dva', 'Peut changer d’angle rapidement et contester les positions qui soutiennent la ligne de front.'],
                ['winston', 'Peut ignorer une partie de la ligne frontale pour atteindre les soutiens ou les angles arrière.'],
                ['sigma', 'Peut opposer une pression à distance si le combat se joue sur une ligne de tir.'],
            ],
            'damage' => [
                ['pharah', 'Peut contourner une défense frontale en attaquant depuis un angle vertical.'],
                ['echo', 'Peut exploiter la verticalité pour atteindre des cibles derrière la ligne de front.'],
                ['mei', 'Peut couper temporairement une ligne de front et isoler une cible si le mur est bien placé.'],
            ],
            'support' => [
                ['ana', 'Peut gêner une avancée groupée avec sa grenade et soutenir à distance.'],
                ['zenyatta', 'Peut aider l’équipe à concentrer ses dégâts sur une cible exposée.'],
                ['baptiste', 'Peut soutenir une position défensive et contribuer aux dégâts à distance.'],
            ],
        ],
        'poke' => [
            'tank' => [
                ['winston', 'Peut fermer rapidement la distance et forcer les tireurs à abandonner leur position.'],
                ['dva', 'Peut atteindre un angle isolé et couvrir l’équipe pendant la rotation.'],
                ['doomfist', 'Peut créer une entrée rapide sur une position arrière mal protégée.'],
            ],
            'damage' => [
                ['tracer', 'Peut contourner les lignes de tir et obliger les tireurs à se retourner.'],
                ['genji', 'Peut utiliser les hauteurs et les angles latéraux pour mettre la pression sur les tireurs.'],
                ['venture', 'Peut contourner une ligne de tir et menacer une position isolée.'],
            ],
            'support' => [
                ['lucio', 'Peut accélérer une traversée entre les couvertures et réduire le temps passé sous le feu.'],
                ['kiriko', 'Peut accompagner une rotation et rejoindre une cible qui s’est engagée.'],
                ['moira', 'Peut traverser une ligne de tir avec son évasion et soutenir une entrée rapprochée.'],
            ],
        ],
        'sniper' => [
            'tank' => [
                ['winston', 'Peut contester une hauteur et obliger le tireur à changer de position.'],
                ['dva', 'Peut approcher rapidement et aider son équipe à traverser une ligne de tir.'],
                ['doomfist', 'Peut atteindre une position isolée et perturber le tir à longue portée.'],
            ],
            'damage' => [
                ['tracer', 'Peut attaquer par les côtés et forcer le tireur à interrompre sa visée.'],
                ['genji', 'Peut exploiter les hauteurs pour réduire l’espace de sécurité du tireur.'],
                ['venture', 'Peut forcer le tireur à abandonner une position isolée en attaquant par un angle latéral.'],
            ],
            'support' => [
                ['lucio', 'Peut accélérer l’équipe d’une couverture à l’autre pour réduire l’exposition.'],
                ['kiriko', 'Peut accompagner un coéquipier qui traverse une ligne de tir ou engage le tireur.'],
                ['brigitte', 'Peut sécuriser les alliés proches quand le tireur est contesté par un flanqueur.'],
            ],
        ],
        'support' => [
            'tank' => [
                ['winston', 'Peut mettre la pression sur la ligne arrière et obliger les soutiens à se séparer.'],
                ['dva', 'Peut poursuivre un soutien isolé tout en protégeant ses alliés des tirs.'],
                ['doomfist', 'Peut déplacer un soutien et perturber la formation adverse.'],
            ],
            'damage' => [
                ['tracer', 'Peut user les ressources défensives du soutien en attaquant depuis plusieurs angles.'],
                ['sombra', 'Peut perturber le plan de jeu du soutien et ouvrir une fenêtre à son équipe.'],
                ['genji', 'Peut suivre un soutien qui tente de fuir et menacer les positions en hauteur.'],
            ],
            'support' => [
                ['brigitte', 'Peut protéger ses propres soutiens contre une pression similaire.'],
                ['ana', 'Peut réduire la valeur d’un soin adverse si sa grenade touche au bon moment.'],
                ['zenyatta', 'Peut aider l’équipe à concentrer ses dégâts sur une cible exposée.'],
            ],
        ],
    ];

    /**
     * @param  array<string, mixed>  $hero
     * @param  list<array<string, mixed>>  $roster
     * @return array<string, mixed>
     */
    public function heroGuide(array $hero, array $roster): array
    {
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

        $counters = [];
        foreach (['tank', 'damage', 'support'] as $counterRole) {
            $counters[$counterRole] = [];

            foreach (self::COUNTERS[$profile['group']][$counterRole] as [$candidateKey, $reason]) {
                if ($candidateKey === $key || ! isset($rosterByKey[$candidateKey])) {
                    continue;
                }

                $candidate = $rosterByKey[$candidateKey];
                if (($candidate['role'] ?? null) !== $counterRole) {
                    continue;
                }

                $counters[$counterRole][] = [
                    'key' => $candidateKey,
                    'name' => $candidate['name'],
                    'reason' => $reason,
                ];
            }
        }

        return [
            'styles' => array_map(fn (string $style): array => [
                'key' => $style,
                'label' => $this->styleLabel($style),
            ], $profile['styles']),
            'strengths' => $notes['strengths'],
            'weaknesses' => $notes['weaknesses'],
            'counters' => $counters,
            'confidence' => isset(self::HERO_PROFILES[$key]) ? 'curated' : 'inferred',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function compositionGuide(): array
    {
        return [
            'cycle' => [
                ['key' => 'poke', 'label' => 'Poke', 'beats' => 'brawl', 'description' => 'Use la composition Brawl/Rush à distance avant qu’elle ne puisse engager.'],
                ['key' => 'brawl', 'label' => 'Brawl / Rush', 'beats' => 'dive', 'description' => 'Le groupe compact protège sa ligne arrière et punit les plongeurs au contact.'],
                ['key' => 'dive', 'label' => 'Dive', 'beats' => 'poke', 'description' => 'La mobilité ferme la distance et conteste les positions de tir isolées.'],
            ],
            'caveat' => 'Ce cycle décrit des tendances, pas une règle garantie. La carte, les perks, les temps de recharge, les angles et la coordination peuvent renverser un matchup.',
        ];
    }

    private function styleLabel(string $style): string
    {
        return match ($style) {
            'brawl' => 'Brawl / Rush',
            'dive' => 'Dive',
            'poke' => 'Poke',
            default => 'Hybride',
        };
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
