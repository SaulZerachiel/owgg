<?php

namespace App\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class OpenRouterClient implements AiClient
{
    public function chat(array $messages): AiResult
    {
        $start = microtime(true);

        try {
            // La clé vient de la configuration Laravel, jamais des données envoyées par le navigateur.
            $response = Http::withToken((string) config('services.openrouter.key'))
                ->baseUrl('https://openrouter.ai/api/v1')
                ->timeout((int) config('services.openrouter.timeout', 30))
                ->post('/chat/completions', [
                    'model' => config('services.openrouter.model'),
                    'messages' => $messages,
                    // Cette limite protège le petit crédit disponible contre les réponses trop longues.
                    'max_tokens' => 800,
                    'provider' => [
                        // OpenRouter doit sélectionner un fournisseur sans conservation des données.
                        'zdr' => true,
                        'data_collection' => 'deny',
                    ],
                ]);
        } catch (ConnectionException $exception) {
            // Une absence de réponse est présentée à l'utilisateur comme un délai dépassé.
            throw new AiUnavailableException('timeout', previous: $exception);
        }

        if ($response->status() === 402) {
            // Le code 402 indique généralement que le crédit du compte est épuisé.
            throw new AiUnavailableException('credit');
        }

        if ($response->failed()) {
            // Les autres erreurs HTTP viennent du fournisseur ou de sa configuration.
            throw new AiUnavailableException('provider');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            // Une réponse HTTP valide ne garantit pas que le contenu attendu est présent.
            throw new AiUnavailableException('invalid');
        }

        return new AiResult(
            content: $content,
            promptTokens: (int) $response->json('usage.prompt_tokens', 0),
            completionTokens: (int) $response->json('usage.completion_tokens', 0),
            cost: (float) $response->json('usage.cost', 0),
            durationMs: (int) ((microtime(true) - $start) * 1000),
            model: (string) $response->json('model', config('services.openrouter.model')),
        );
    }
}
