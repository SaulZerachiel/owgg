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

        if (! is_string($player['player_id'] ?? null)) {
            throw new UnexpectedValueException('OverFast returned an invalid player profile.');
        }

        if (($player['is_public'] ?? null) !== true) {
            throw new OverfastProfileNotFoundException(
                "The OverFast profile for [{$battleTag}] is not public.",
            );
        }

        return $player;
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
    public function heroes(): array
    {
        return $this->getList('/heroes');
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
     * @return list<array<string, mixed>>
     */
    private function getList(string $path): array
    {
        $data = $this->get($path);

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
