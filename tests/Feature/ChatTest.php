<?php

use App\Ai\AiClient;
use App\Ai\AiResult;
use App\Ai\OpenRouterClient;
use App\Models\AiCall;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'https://overfast-api.tekrop.fr/*' => function (Request $request) {
            return match (parse_url($request->url(), PHP_URL_PATH)) {
                '/heroes' => Http::response([
                    ['key' => 'ana', 'name' => 'Ana'],
                    ['key' => 'mei', 'name' => 'Mei'],
                    ['key' => 'sombra', 'name' => 'Sombra'],
                ]),
                '/heroes/ana' => Http::response([
                    'name' => 'Ana',
                    'role' => 'support',
                    'subrole' => 'medic',
                    'abilities' => [
                        ['name' => 'Biotic Rifle', 'description' => 'A scoped rifle.'],
                    ],
                    'perks' => [
                        'minor' => [
                            ['name' => 'Ana Minor Perk', 'description' => 'A test perk.'],
                        ],
                        'major' => [],
                    ],
                ]),
                default => Http::response([
                    'name' => 'Hero',
                    'abilities' => [],
                    'perks' => ['minor' => [], 'major' => []],
                ]),
            };
        },
    ]);
});

test('guests cannot use the coach', function () {
    $response = $this->postJson(route('chat.message'), ['message' => 'Bonjour']);

    $response->assertUnauthorized();
});

test('authenticated users can use the fake coach and see their remaining quota', function () {
    $user = User::factory()->create();

    // En test, le conteneur injecte FakeAiClient : aucun appel externe n'est effectué.
    $response = $this->actingAs($user)->postJson(route('chat.message'), [
        'message' => 'Comment améliorer ma visée ?',
    ]);

    $response->assertOk()
        ->assertJsonPath('remaining', 9)
        ->assertJsonStructure([
            'answer',
            'remaining',
            'usage',
            'conversation' => ['id', 'title'],
        ])
        ->assertJsonPath('conversation.title', 'Comment améliorer ma visée ?');
    expect(AiCall::where('user_id', $user->id)->count())->toBe(1);

    $conversationId = $response->json('conversation.id');
    $this->actingAs($user)
        ->getJson(route('chat.conversations.show', $conversationId))
        ->assertOk()
        ->assertJsonPath('messages.0.role', 'user')
        ->assertJsonPath('messages.0.content', 'Comment améliorer ma visée ?')
        ->assertJsonPath('messages.1.role', 'assistant');
});

test('the OpenRouter response budget is configurable for more detailed coaching', function () {
    config([
        'services.openrouter.key' => 'test-key',
        'services.openrouter.model' => 'test/model',
        'services.openrouter.max_tokens' => 1500,
    ]);
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'model' => 'test/model',
            'choices' => [
                ['message' => ['content' => 'Conseil tactique.']],
            ],
            'usage' => [
                'prompt_tokens' => 20,
                'completion_tokens' => 30,
                'cost' => 0.001,
            ],
        ]),
    ]);

    app(OpenRouterClient::class)->chat([
        ['role' => 'user', 'content' => 'Comment exploiter ce matchup ?'],
    ]);

    Http::assertSent(fn (Request $request): bool => $request->data()['max_tokens'] === 1500);
});

test('the coach receives only the authenticated player snapshots and coaching skills', function () {
    $user = User::factory()->create(['battletag' => 'Player-1234']);
    $profile = Profile::factory()->for($user)->create(['player_id' => 'Player-1234']);
    $profile->statSnapshots()->create([
        'fingerprint' => str_repeat('a', 64),
        'snapshot' => ['all_heroes' => ['games_played' => 100]],
        'captured_at' => now()->subDay(),
    ]);
    $profile->statSnapshots()->create([
        'fingerprint' => str_repeat('b', 64),
        'snapshot' => [
            'all_heroes' => ['games_played' => 123],
            'heroes' => [['hero' => 'Ana', 'metrics' => ['win_percentage' => 60]]],
        ],
        'captured_at' => now(),
    ]);

    $otherUser = User::factory()->create(['battletag' => 'Other-5678']);
    $otherProfile = Profile::factory()->for($otherUser)->create(['player_id' => 'Other-5678']);
    $otherProfile->statSnapshots()->create([
        'fingerprint' => str_repeat('c', 64),
        'snapshot' => ['all_heroes' => ['games_played' => 987]],
        'captured_at' => now(),
    ]);

    $client = new class implements AiClient
    {
        /** @var array<int, array{role: string, content: string}> */
        public array $messages = [];

        public function chat(array $messages): AiResult
        {
            $this->messages = $messages;

            return new AiResult('Analyse reçue.', 10, 10, 0, 1, 'fake');
        }
    };
    $this->app->instance(AiClient::class, $client);

    $this->actingAs($user)->postJson(route('chat.message'), [
        'message' => 'Analyse ma progression sur Ana.',
    ])->assertOk();

    $systemContext = collect($client->messages)
        ->where('role', 'system')
        ->pluck('content')
        ->implode("\n");

    expect($systemContext)
        ->toContain('"games_played":123')
        ->toContain('"games_played":100')
        ->toContain('snapshot précédent')
        ->toContain('Pour analyser les performances d’un héros')
        ->toContain('Ana Minor Perk')
        ->not->toContain('"games_played":987');
});

test('the coach refuses requests after the daily quota', function () {
    $user = User::factory()->create();

    // On simule les dix tentatives déjà consommées aujourd'hui.
    foreach (range(1, 10) as $_) {
        AiCall::create(['user_id' => $user->id, 'model' => 'fake']);
    }

    $response = $this->actingAs($user)->postJson(route('chat.message'), [
        'message' => 'Encore une question',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('message');
});
