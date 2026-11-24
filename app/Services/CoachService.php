<?php

namespace App\Services;

use App\Ai\AiClient;
use App\Ai\AiResult;
use App\Ai\AiUnavailableException;
use App\Ai\CoachHarness;
use App\Models\AiCall;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class CoachService
{
    public function __construct(
        private readonly AiClient $client,
        private readonly CoachHarness $harness,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{answer: string, title: string|null, remaining: int, usage: array<string, int|float|string>}
     */
    public function answer(
        User $user,
        string $message,
        array $history = [],
        bool $generateTitle = false,
    ): array {
        $quota = (int) config('services.openrouter.daily_quota', 10);
        $remaining = $this->remaining($user);

        // Le quota est vérifié avant l'appel afin de ne jamais dépenser un crédit inutilement.
        if ($remaining === 0) {
            throw ValidationException::withMessages([
                'message' => ["Vous avez utilisé vos {$quota} essais pour aujourd'hui."],
            ]);
        }

        $messages = $this->harness->messages($user, $message, $history, $generateTitle);

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
