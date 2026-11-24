<?php

use App\Models\AiCall;
use App\Models\User;

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
