<?php

use App\Services\CoachGameKnowledgeBuilder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('the coach receives current abilities and perks for heroes in the conversation', function () {
    Http::fake(function (Request $request) {
        return match (parse_url($request->url(), PHP_URL_PATH)) {
            '/heroes' => Http::response([
                ['key' => 'mei', 'name' => 'Mei'],
                ['key' => 'sombra', 'name' => 'Sombra'],
                ['key' => 'ana', 'name' => 'Ana'],
            ]),
            '/heroes/mei' => Http::response([
                'name' => 'Mei',
                'role' => 'damage',
                'subrole' => 'specialist',
                'abilities' => [
                    ['name' => 'Ice Wall', 'description' => 'Create a wall in front of you.'],
                    ['name' => 'Blizzard', 'description' => 'Launch a weather control drone.'],
                ],
                'perks' => [
                    'minor' => [
                        ['name' => 'Skating Rink', 'description' => 'Allies inside Blizzard heal.'],
                    ],
                    'major' => [
                        ['name' => 'Cryo-Storm', 'description' => 'Cryo-Freeze damages nearby enemies.'],
                    ],
                ],
            ]),
            '/heroes/sombra' => Http::response([
                'name' => 'Sombra',
                'role' => 'support',
                'subrole' => 'tactician',
                'abilities' => [
                    ['name' => 'EMP', 'description' => 'Hack nearby enemies and destroy barriers.'],
                ],
                'perks' => [
                    'minor' => [
                        ['name' => 'Life Hack', 'description' => 'EMP applies Hotfix to nearby allies.'],
                    ],
                    'major' => [],
                ],
            ]),
            default => Http::response([], 404),
        };
    });

    $builder = app(CoachGameKnowledgeBuilder::class);
    $history = [
        ['role' => 'user', 'content' => 'Je joue Mei en compétitif.'],
        ['role' => 'assistant', 'content' => 'Quel perk utilises-tu ?'],
    ];

    $context = $builder->build('Sombra peut-elle annuler Blizzard avec EMP ?', $history);
    $builder->build('Sombra peut-elle annuler Blizzard avec EMP ?', $history);

    expect($context)
        ->toContain('OverFast')
        ->toContain('"name":"Ice Wall"')
        ->toContain('"name":"EMP"')
        ->toContain('"name":"Skating Rink"')
        ->toContain('"name":"Cryo-Storm"');

    Http::assertSentCount(3);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/heroes?locale=fr-fr'));
});

test('the coach can answer when OverFast game data is temporarily unavailable', function () {
    Http::fake(fn () => Http::response([], 503));

    $context = app(CoachGameKnowledgeBuilder::class)->build('Comment jouer Mei ?');

    expect($context)
        ->toContain('temporairement indisponibles')
        ->toContain('ne présente pas les capacités ou perks comme vérifiés à jour');
});
