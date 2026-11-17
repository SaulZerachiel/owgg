<?php

namespace App\Http\Controllers;

use App\Services\CoachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(private readonly CoachService $coach) {}

    public function page(Request $request): Response
    {
        // Le quota initial est calculé côté serveur pour éviter d'afficher une valeur obsolète.
        return Inertia::render('Chat', [
            'remaining' => $this->coach->remaining($request->user()),
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        // La validation bloque les messages vides, trop longs ou mal formés avant l'appel IA.
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['sometimes', 'array', 'max:10'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        return response()->json($this->coach->answer(
            $request->user(),
            $data['message'],
            $data['history'] ?? [],
        ));
    }
}
