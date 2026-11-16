<?php

namespace App\Ai;

final class FakeAiClient implements AiClient
{
    public function chat(array $messages): AiResult
    {
        // Les tests utilisent cette réponse pour ne jamais appeler l'API ni dépenser de crédit.
        return new AiResult(
            content: 'Réponse simulée : votre message a bien été reçu.',
            promptTokens: 10,
            completionTokens: 10,
            cost: 0,
            durationMs: 1,
            model: 'fake',
        );
    }
}
