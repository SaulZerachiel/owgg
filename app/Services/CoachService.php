<?php

namespace App\Services;

use App\Ai\AiClient;
use App\Ai\AiResult;
use App\Ai\AiUnavailableException;
use App\Models\AiCall;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class CoachService
{
    public function __construct(private readonly AiClient $client) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{answer: string, title: string|null, remaining: int, usage: array<string, int|float|string>}
     */
    public function answer(
        User $user,
        string $message,
        array $history = [],
        bool $generateTitle = false,
    ): array
    {
        $quota = (int) config('services.openrouter.daily_quota', 10);
        $remaining = $this->remaining($user);

        // Le quota est vérifié avant l'appel afin de ne jamais dépenser un crédit inutilement.
        if ($remaining === 0) {
            throw ValidationException::withMessages([
                'message' => ["Vous avez utilisé vos {$quota} essais pour aujourd'hui."],
            ]);
        }

        $messages = [
            [
                // Ce message définit le rôle et la langue attendue pour toutes les conversations.
                'role' => 'system',
                'content' => (string) config('winston.system_prompt'),
            ],
            ...($generateTitle ? [[
                'role' => 'system',
                'content' => 'Pour cette première réponse, commence par une ligne exactement au format [[TITLE: titre court]], avec 3 à 6 mots décrivant le sujet. N’utilise ni Markdown ni point final dans le titre. Écris ensuite ta réponse normalement sur la ligne suivante.',
            ]] : []),
            // On limite l'historique pour maîtriser la taille et le coût de la requête.
            ...array_slice($history, -10),
            ['role' => 'user', 'content' => $message],
        ];

        try {
            $result = $this->client->chat($messages);
        } catch (AiUnavailableException $exception) {
            // Les échecs sont aussi journalisés pour suivre les incidents et les tentatives consommées.
            AiCall::create([
                'user_id' => $user->id,
                'model' => (string) config('services.openrouter.model'),
                'success' => false,
                'error_code' => $exception->errorCode,
            ]);

            throw $exception;
        }

        $this->record($user, $result);
        [$title, $answer] = $this->extractTitle($result->content);
        if ($generateTitle && $title === null) {
            $title = $this->fallbackTitle($message);
        }

        return [
            'answer' => $answer,
            'title' => $title,
            'remaining' => $remaining - 1,
            'usage' => [
                'model' => $result->model,
                'prompt_tokens' => $result->promptTokens,
                'completion_tokens' => $result->completionTokens,
                'cost' => $result->cost,
                'duration_ms' => $result->durationMs,
            ],
        ];
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function extractTitle(string $content): array
    {
        if (preg_match('/^\s*\[\[TITLE:\s*(.{3,120}?)\]\]\s*(?:\r?\n)+/i', $content, $matches) !== 1) {
            return [null, $content];
        }

        $title = trim((string) preg_replace('/\s+/', ' ', $matches[1]));
        $answer = ltrim(substr($content, strlen($matches[0])));

        return [$title !== '' ? $title : null, $answer];
    }

    private function fallbackTitle(string $message): string
    {
        $title = trim((string) preg_replace('/\s+/', ' ', $message));

        return mb_substr($title, 0, 60);
    }

    public function remaining(User $user): int
    {
        $quota = (int) config('services.openrouter.daily_quota', 10);
        $used = AiCall::query()
            // Les appels sont remis à zéro automatiquement chaque jour civil.
            ->where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return max(0, $quota - $used);
    }

    private function record(User $user, AiResult $result): void
    {
        // Les métriques retournées par OpenRouter permettent ensuite d'analyser le coût réel.
        AiCall::create([
            'user_id' => $user->id,
            'model' => $result->model,
            'prompt_tokens' => $result->promptTokens,
            'completion_tokens' => $result->completionTokens,
            'cost' => $result->cost,
            'duration_ms' => $result->durationMs,
            'success' => true,
        ]);
    }
}
