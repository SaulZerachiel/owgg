<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class OverfastService
{
    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function playerByBattleTag(string $battleTag): array
    {
        $player = $this->getObject('/players/'.rawurlencode($battleTag));
        $summary = $player['summary'] ?? null;

        if (! is_array($summary) || ! is_string($summary['username'] ?? null)) {
            throw new UnexpectedValueException('OverFast returned an invalid player profile.');
        }

        if (! array_key_exists('stats', $player)) {
            throw new UnexpectedValueException('OverFast returned an invalid player profile.');
        }

        if ($player['stats'] === null) {
            throw new OverfastProfileNotFoundException(
                "The OverFast profile for [{$battleTag}] is private or has no statistics.",
            );
        }

        if (! is_array($player['stats'])) {
            throw new UnexpectedValueException('OverFast returned an invalid player profile.');
        }

        return [
            'player_id' => $battleTag,
            'name' => $summary['username'],
            'avatar' => $summary['avatar'] ?? null,
            'namecard' => $summary['namecard'] ?? null,
            'title' => $summary['title'] ?? null,
            'career_url' => rtrim(config('services.overfast.base_url'), '/').'/players/'.rawurlencode($battleTag),
            'blizzard_id' => null,
            'last_updated_at' => $summary['last_updated_at'] ?? null,
            'is_public' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function playerSummary(string $playerId): array
    {
        return $this->getObject('/players/'.rawurlencode($playerId).'/summary');
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function playerStatsSummary(string $playerId): array
    {
        return $this->getObject('/players/'.rawurlencode($playerId).'/stats/summary', [
            'gamemode' => 'competitive',
            'platform' => 'pc',
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function playerCareerStats(string $playerId): array
    {
        return $this->getObject('/players/'.rawurlencode($playerId).'/stats/career', [
            'gamemode' => 'competitive',
            'platform' => 'pc',
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function roles(): array
    {
        return $this->getList('/roles');
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function heroes(string $locale = 'en-us'): array
    {
        return $this->getList('/heroes', ['locale' => $locale]);
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function maps(): array
    {
        return $this->getList('/maps');
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function heroStats(
        string $platform,
        string $gamemode,
        string $region,
        ?string $map = null,
    ): array {
        $query = [
            'platform' => $platform,
            'gamemode' => $gamemode,
            'region' => $region,
            'order_by' => 'winrate:desc',
        ];

        if ($map !== null) {
            $query['map'] = $map;
        }

        return $this->getList('/heroes/stats', $query);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    public function hero(string $heroKey, string $locale = 'en-us'): array
    {
        return $this->getObject('/heroes/'.rawurlencode($heroKey), [
            'locale' => $locale,
        ]);
    }

    /**
     * @param  array<string, string>  $query
     * @return array<array-key, mixed>
     *
     * @throws ConnectionException
     * @throws OverfastProfileNotFoundException
     */
    private function get(string $path, array $query = []): array
    {
        $response = Http::baseUrl(rtrim(config('services.overfast.base_url'), '/'))
            ->timeout(10)
            ->retry(3, 250, throw: false)
            ->get($path, $query);

        if ($response->notFound() && str_starts_with($path, '/players')) {
            throw new OverfastProfileNotFoundException(
                'OverFast has no public statistics for this player.',
            );
        }

        $data = $response->throw()->json();

        if (! is_array($data)) {
            throw new UnexpectedValueException('OverFast returned an invalid response.');
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $query
     * @return array<string, mixed>
     */
    private function getObject(string $path, array $query = []): array
    {
        return $this->stringKeyedArray($this->get($path, $query));
    }

    /**
     * @param  array<string, string>  $query
     * @return list<array<string, mixed>>
     */
    private function getList(string $path, array $query = []): array
    {
        $data = $this->get($path, $query);

        if (! array_is_list($data)) {
            throw new UnexpectedValueException('OverFast returned an invalid list response.');
        }

        $items = [];

        foreach ($data as $item) {
            if (! is_array($item)) {
                throw new UnexpectedValueException('OverFast returned an invalid list item.');
            }

            $items[] = $this->stringKeyedArray($item);
        }

        return $items;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<string, mixed>
     */
    private function stringKeyedArray(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (! is_string($key)) {
                throw new UnexpectedValueException('OverFast returned an invalid object response.');
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
