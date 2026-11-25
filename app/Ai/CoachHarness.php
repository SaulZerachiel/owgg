<?php

namespace App\Ai;

use App\Models\User;
use App\Services\CoachGameKnowledgeBuilder;
use App\Services\CoachStatsContextBuilder;

final class CoachHarness
{
    public function __construct(
        private readonly CoachSkillRegistry $skills,
        private readonly CoachStatsContextBuilder $stats,
        private readonly CoachGameKnowledgeBuilder $gameKnowledge,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    public function messages(User $user, string $message, array $history = [], bool $generateTitle = false): array
    {
        return [
            [
                'role' => 'system',
                'content' => (string) config('winston.system_prompt'),
            ],
            [
                'role' => 'system',
                'content' => "Modules de coaching à appliquer selon la question :\n".$this->skills->instructions(),
            ],
            [
                'role' => 'system',
                'content' => $this->gameKnowledge->build($message, $history),
            ],
            [
                'role' => 'system',
                'content' => $this->stats->build($user),
            ],
            ...($generateTitle ? [[
                'role' => 'system',
                'content' => 'Pour cette première réponse, commence par une ligne exactement au format [[TITLE: titre court]], avec 3 à 6 mots décrivant le sujet. N’utilise ni Markdown ni point final dans le titre. Écris ensuite ta réponse normalement sur la ligne suivante.',
            ]] : []),
            ...array_slice($history, -10),
            ['role' => 'user', 'content' => $message],
        ];
    }
}
