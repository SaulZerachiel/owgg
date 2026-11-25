<?php

namespace App\Http\Controllers;

use App\Services\CounterwatchGuide;
use App\Services\OverfastService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use UnexpectedValueException;

class CounterwatchController extends Controller
{
    private const LOCALE = 'fr-fr';

    public function __construct(
        private readonly OverfastService $overfast,
        private readonly CounterwatchGuide $guide,
    ) {}

    public function show(Request $request): Response
    {
        $catalogError = null;

        try {
            $catalog = Cache::remember(
                'counterwatch:heroes:'.self::LOCALE,
                now()->addHours(6),
                fn (): array => $this->overfast->heroes(self::LOCALE),
            );
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);
            $catalog = [];
            $catalogError = 'Le roster OverFast est momentanément indisponible. Réessaie dans quelques instants.';
        }

        $roster = array_values(array_filter(
            $catalog,
            static fn (array $hero): bool => is_string($hero['key'] ?? null)
                && is_string($hero['name'] ?? null)
                && is_string($hero['role'] ?? null),
        ));

        if ($catalogError === null && $roster === []) {
            $catalogError = 'Aucun héros valide n’a été reçu du roster OverFast.';
        }

        $mapCatalogError = null;

        try {
            $mapCatalog = Cache::remember(
                'counterwatch:maps:v2',
                now()->addHours(6),
                fn (): array => $this->overfast->maps(),
            );
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);
            $mapCatalog = [];
            $mapCatalogError = 'Les données des cartes sont momentanément indisponibles.';
        }

        $maps = array_values(array_filter(
            $mapCatalog,
            static fn (array $map): bool => is_string($map['key'] ?? null)
                && is_string($map['name'] ?? null)
                && is_array($map['uses'] ?? null)
                && in_array('standard', $map['uses'], true),
        ));

        if ($mapCatalogError === null && $maps === []) {
            $mapCatalogError = 'Aucune carte du mode standard n’a été reçue d’OverFast.';
        }

        $rosterByKey = [];
        foreach ($roster as $hero) {
            $rosterByKey[$hero['key']] = $hero;
        }

        $mapsByKey = [];
        foreach ($maps as $map) {
            $mapsByKey[$map['key']] = $map;
        }

        $requestedMap = $request->query('map');
        if ($requestedMap !== null) {
            abort_unless(is_string($requestedMap) && isset($mapsByKey[$requestedMap]), 404);
        }
        $selectedMapKey = is_string($requestedMap) ? $requestedMap : null;
        $selectedMapName = $selectedMapKey !== null ? $mapsByKey[$selectedMapKey]['name'] : null;

        $heroStatsError = null;
        $heroStatsByKey = [];
        if ($roster !== []) {
            try {
                $heroStats = Cache::remember(
                    'counterwatch:hero-stats:pc:competitive:europe:'.($selectedMapKey ?? 'all'),
                    now()->addHour(),
                    fn (): array => $this->overfast->heroStats(
                        platform: 'pc',
                        gamemode: 'competitive',
                        region: 'europe',
                        map: $selectedMapKey,
                    ),
                );
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                report($exception);
                $heroStats = [];
                $heroStatsError = 'Les statistiques OverFast sont momentanément indisponibles.';
            }

            foreach ($heroStats as $entry) {
                if (! is_string($entry['hero'] ?? null)
                    || ! isset($rosterByKey[$entry['hero']])
                    || ! is_numeric($entry['winrate'] ?? null)) {
                    continue;
                }

                $heroStatsByKey[$entry['hero']] = [
                    'winrate' => (float) $entry['winrate'],
                    'pickrate' => is_numeric($entry['pickrate'] ?? null)
                        ? (float) $entry['pickrate']
                        : null,
                ];
            }

            if ($heroStatsError === null && $heroStatsByKey === []) {
                $heroStatsError = 'Aucune statistique exploitable n’a été reçue d’OverFast.';
            }
        }

        $matchups = $roster !== [] ? $this->guide->matchups() : [];
        $requestedHero = $request->query('hero');
        $selectedHeroKey = is_string($requestedHero) && isset($rosterByKey[$requestedHero])
                ? $requestedHero
                : ($roster[0]['key'] ?? null);

        return Inertia::render('Counterwatch/Index', [
            'heroes' => array_map(
                fn (array $hero): array => [
                    'key' => $hero['key'],
                    'name' => $hero['name'],
                    'role' => $hero['role'],
                    'subrole' => $hero['subrole'] ?? null,
                    'guide' => $this->guide->heroGuide(
                        $hero,
                        $roster,
                        $maps,
                        $matchups,
                        $heroStatsByKey,
                    ),
                ],
                $roster,
            ),
            'catalogError' => $catalogError,
            'mapCatalogError' => $mapCatalogError,
            'heroStatsError' => $heroStatsError,
            'availableMaps' => array_map(
                static fn (array $hero): array => [
                    'key' => $hero['key'],
                    'name' => $hero['name'],
                ],
                $maps,
            ),
            'tierList' => array_map(
                fn (string $heroKey): array => [
                    'key' => $heroKey,
                    'name' => $rosterByKey[$heroKey]['name'],
                    'role' => $rosterByKey[$heroKey]['role'],
                    ...$heroStatsByKey[$heroKey],
                ],
                array_keys($heroStatsByKey),
            ),
            'selectedMapKey' => $selectedMapKey,
            'selectedMapName' => $selectedMapName,
            'selectedHeroKey' => $selectedHeroKey,
            'statsScope' => 'PC · compétitif · Europe · toutes divisions',
            'compositionGuide' => $this->guide->compositionGuide(),
            'dataNotice' => 'Le roster, les statistiques et les cartes viennent d’OverFast. Les matchups OWGG sont des recommandations éditoriales et le winrate n’est pas un taux de victoire contre le héros sélectionné.',
        ]);
    }
}
