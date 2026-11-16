<?php

namespace App\Ai;

use RuntimeException;
use Throwable;

final class AiUnavailableException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, ?Throwable $previous = null)
    {
        // Ces codes permettent de distinguer une erreur utilisateur d'une panne du fournisseur.
        parent::__construct(match ($errorCode) {
            'timeout' => 'Le service IA a mis trop de temps à répondre.',
            'credit' => 'Le crédit du service IA est épuisé.',
            'provider' => 'Le fournisseur IA est momentanément indisponible.',
            default => 'La réponse du service IA est inexploitable.',
        }, previous: $previous);
    }
}
