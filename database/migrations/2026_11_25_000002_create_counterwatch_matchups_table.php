<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MATCHUPS = [
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

    public function up(): void
    {
        Schema::create('counterwatch_matchups', function (Blueprint $table): void {
            $table->id();
            $table->string('target_archetype', 32);
            $table->string('role', 16);
            $table->string('counter_hero_key', 64);
            $table->unsignedSmallInteger('priority');
            $table->text('reason');
            $table->unique(
                ['target_archetype', 'role', 'counter_hero_key'],
                'cw_matchups_archetype_role_hero_unique',
            );
            $table->unique(
                ['target_archetype', 'role', 'priority'],
                'cw_matchups_archetype_role_priority_unique',
            );
        });

        $rows = [];

        foreach (self::MATCHUPS as $archetype => $roles) {
            foreach ($roles as $role => $counters) {
                foreach ($counters as $index => [$heroKey, $reason]) {
                    $rows[] = [
                        'target_archetype' => $archetype,
                        'role' => $role,
                        'counter_hero_key' => $heroKey,
                        'priority' => $index + 1,
                        'reason' => $reason,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('counterwatch_matchups')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('counterwatch_matchups');
    }
};
