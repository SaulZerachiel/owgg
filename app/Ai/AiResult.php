<?php

namespace App\Ai;

final readonly class AiResult
{
    // On regroupe la réponse et les métriques utiles pour l'affichage et le suivi des coûts.
    public function __construct(
        public string $content,
        public int $promptTokens,
        public int $completionTokens,
        public float $cost,
        public int $durationMs,
        public string $model,
    ) {}
}
