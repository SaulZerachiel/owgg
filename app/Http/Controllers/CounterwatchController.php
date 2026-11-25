<?php

namespace App\Http\Controllers;

use App\Services\CounterwatchGuide;
use App\Services\OverfastService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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

    public function show(): Response
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

        return Inertia::render('Counterwatch/Index', [
            'heroes' => array_map(
                fn (array $hero): array => [
                    'key' => $hero['key'],
                    'name' => $hero['name'],
                    'role' => $hero['role'],
                    'subrole' => $hero['subrole'] ?? null,
                    'guide' => $this->guide->heroGuide($hero, $roster),
                ],
                $roster,
            ),
            'catalogError' => $catalogError,
            'compositionGuide' => $this->guide->compositionGuide(),
            'dataNotice' => 'Le roster et les rôles viennent d’OverFast. Les profils et suggestions sont une synthèse tactique OWGG, pas un classement statistique ni une garantie de victoire.',
        ]);
    }
}
