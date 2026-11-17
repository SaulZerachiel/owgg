<?php

namespace App\Ai;

interface AiClient
{
    // Le reste de l'application dépend de ce contrat, pas d'OpenRouter directement.
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages): AiResult;
}
