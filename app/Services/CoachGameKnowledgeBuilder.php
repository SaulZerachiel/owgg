<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use UnexpectedValueException;

final class CoachGameKnowledgeBuilder
{
    private const LOCALE = 'fr-fr';

    private const MAX_HEROES = 4;

    private const CACHE_TTL_HOURS = 6;

    public function __construct(
        private readonly OverfastService $overfast,
        private readonly CounterwatchGuide $counterwatch,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     *
     * @throws JsonException
     */
    public function build(string $message, array $history = []): string
    {
        $query = implode("\n", [
            ...array_map(
                static fn (array $entry): string => $entry['content'],
                array_slice($history, -10),
            ),
            $message,
        ]);

        try {
            $catalog = Cache::remember(
                'coach-game-knowledge:heroes:'.self::LOCALE,
                now()->addHours(self::CACHE_TTL_HOURS),
                fn (): array => [
                    'fetched_at' => now()->toIso8601String(),
                    'heroes' => $this->overfast->heroes(self::LOCALE),
                ],
            );
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);

            return $this->unavailableContext();
        }

        $matchedHeroes = array_values(array_filter(
            $catalog['heroes'],
            fn (array $hero): bool => is_string($hero['key'] ?? null)
                && is_string($hero['name'] ?? null)
                && $this->mentionsHero($query, $hero['name'], $hero['key']),
        ));
        $mapDataUnavailable = false;
        $maps = [];

        if ($matchedHeroes !== []) {
            try {
                $maps = Cache::remember(
                    'counterwatch:maps:v2',
                    now()->addHours(self::CACHE_TTL_HOURS),
                    fn (): array => $this->overfast->maps(),
                );
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                report($exception);
                Log::warning('Current OverFast map data is unavailable to the coach.');
                $mapDataUnavailable = true;
            }
        }

        $matchups = $matchedHeroes !== [] ? $this->counterwatch->matchups() : [];
        $archetypeMatchups = $matchedHeroes !== [] ? $this->counterwatch->archetypeMatchups() : [];
        $heroMapRecommendations = $matchedHeroes !== [] ? $this->counterwatch->heroMapRecommendations() : [];
        $knowledge = [];
        $unavailableHeroes = [];

        foreach (array_slice($matchedHeroes, 0, self::MAX_HEROES) as $hero) {
            $heroKey = $hero['key'];

            try {
                $heroData = Cache::remember(
                    'coach-game-knowledge:hero:'.self::LOCALE.':'.$heroKey,
                    now()->addHours(self::CACHE_TTL_HOURS),
                    function () use ($heroKey): array {
                        $data = $this->overfast->hero($heroKey, self::LOCALE);

                        if (! is_string($data['name'] ?? null)
                            || ! is_array($data['abilities'] ?? null)
                            || ! is_array($data['perks'] ?? null)) {
                            throw new UnexpectedValueException("OverFast returned invalid coaching data for hero [{$heroKey}].");
                        }

                        return [
                            'fetched_at' => now()->toIso8601String(),
                            'data' => $data,
                        ];
                    },
                );
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                report($exception);
                Log::warning('Current OverFast hero data is unavailable to the coach.', [
                    'hero_key' => $heroKey,
                ]);
                $unavailableHeroes[] = $hero['name'];

                continue;
            }

            $knowledge[] = [
                'fetched_at' => $heroData['fetched_at'],
                ...$this->relevantDetails($heroData['data']),
                'counterwatch_guide' => $this->counterwatch->heroGuide(
                    $hero,
                    $catalog['heroes'],
                    $maps,
                    $matchups,
                    $archetypeMatchups,
                    $heroMapRecommendations,
                ),
            ];
        }

        return 'Référence de jeu actualisée pour les héros évoqués (OverFast, fiches Blizzard; cache de six heures). '
            .'Les Perks standard et les pouvoirs Stadium sont deux systèmes distincts. '
            .'Les fiches donnent les capacités et Perks actuels, pas toutes les interactions cachées : '
            .'distingue les faits de fiche de tes déductions tactiques, et signale les interactions dépendantes du patch. '
            .'Les matchups Counterwatch combinent des relations héros-à-héros et des pistes liées au style de jeu, complétées si besoin par d’autres archétypes : ce sont des indications éditoriales, pas des statistiques de duel ni des certitudes; '
            .'les fiches Counterwatch ont été consultées pour l’ensemble du roster; leurs winrates ne sont pas repris et les conseils OWGG restent des repères éditoriaux, pas des certitudes; '
            .'les tendances de performance peuvent évoluer sans rework et leurs taux ne doivent pas être présentés comme des faits de matchup. '
            .'Conserve les noms Brawl, Dive et Poke : ne traduis pas Dive par « plongée » ou « plongeur », '
            .'et n’ajoute pas « Rush » après Brawl. '
            .'Si map_data_unavailable vaut true, signale que les informations de carte manquent au lieu de les inventer. '
            ."Nuance les conseils selon la carte, les compositions, les perks et l’exécution.\n"
            .'Données : '.json_encode([
                'heroes' => $knowledge,
                'hero_data_unavailable' => $unavailableHeroes,
                'catalog_fetched_at' => $catalog['fetched_at'],
                'map_data_unavailable' => $mapDataUnavailable,
                'composition_guide' => $this->counterwatch->compositionGuide(),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    private function mentionsHero(string $query, string $name, string $key): bool
    {
        $normalizedQuery = ' '.$this->normalize($query).' ';

        foreach ([$name, $key] as $variant) {
            $normalizedVariant = $this->normalize($variant);
            if ($normalizedVariant === '') {
                continue;
            }

            if (str_contains($normalizedQuery, ' '.$normalizedVariant.' ')) {
                return true;
            }

            $compactVariant = str_replace(' ', '', $normalizedVariant);
            if ($compactVariant !== $normalizedVariant
                && in_array($compactVariant, explode(' ', trim($normalizedQuery)), true)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        $ascii = Str::ascii(mb_strtolower($value));

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $ascii));
    }

    /**
     * @param  array<string, mixed>  $hero
     * @return array<string, mixed>
     */
    private function relevantDetails(array $hero): array
    {
        return [
            'name' => $hero['name'] ?? null,
            'role' => $hero['role'] ?? null,
            'subrole' => $hero['subrole'] ?? null,
            'abilities' => $this->namedDescriptions($hero['abilities'] ?? []),
            'perks' => [
                'minor' => $this->namedDescriptions($hero['perks']['minor'] ?? []),
                'major' => $this->namedDescriptions($hero['perks']['major'] ?? []),
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, description: string}>
     */
    private function namedDescriptions(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $descriptions = [];
        foreach ($items as $item) {
            if (is_array($item)
                && is_string($item['name'] ?? null)
                && is_string($item['description'] ?? null)) {
                $descriptions[] = [
                    'name' => $item['name'],
                    'description' => $item['description'],
                ];
            }
        }

        return $descriptions;
    }

    private function unavailableContext(): string
    {
        return 'Les fiches de héros Blizzard via OverFast sont temporairement indisponibles. '
            .'Réponds aux questions tactiques avec prudence et ne présente pas les capacités ou perks comme vérifiés à jour.';
    }
}
