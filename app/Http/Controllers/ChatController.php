<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\User;
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
        $user = $this->authenticatedUser($request);

        // Le quota initial est calculé côté serveur pour éviter d'afficher une valeur obsolète.
        return Inertia::render('Chat', [
            'remaining' => $this->coach->remaining($user),
            'conversations' => $user
                ->chatConversations()
                ->latest('updated_at')
                ->get(['id', 'title', 'updated_at']),
        ]);
    }

    public function conversation(Request $request, ChatConversation $conversation): JsonResponse
    {
        abort_unless($conversation->user_id === $this->authenticatedUser($request)->id, 404);

        return response()->json([
            'conversation' => $conversation->only(['id', 'title']),
            'messages' => $conversation->messages()
                ->oldest('created_at')
                ->get(['role', 'content']),
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        // La validation bloque les messages vides, trop longs ou mal formés avant l'appel IA.
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'uuid'],
        ]);

        $user = $this->authenticatedUser($request);
        $conversation = null;
        if (isset($data['conversation_id']) && is_string($data['conversation_id'])) {
            $conversation = ChatConversation::query()
                ->where('user_id', $user->id)
                ->whereKey($data['conversation_id'])
                ->firstOrFail();
        }
        $history = $conversation?->messages()
            ->oldest('created_at')
            ->get(['role', 'content'])
            ->map(fn ($item): array => [
                'role' => $item->role,
                'content' => $item->content,
            ])
            ->all() ?? [];

        $result = $this->coach->answer(
            $user,
            $data['message'],
            $history,
            $conversation === null || $conversation->messages()->doesntExist(),
        );

        $conversation ??= ChatConversation::create([
            'user_id' => $user->id,
            'title' => $result['title'] ?? 'Nouvelle conversation',
        ]);
        $conversation->messages()->createMany([
            ['role' => 'user', 'content' => $data['message']],
            ['role' => 'assistant', 'content' => $result['answer']],
        ]);
        if ($result['title'] !== null) {
            $conversation->update(['title' => $result['title']]);
        }

        return response()->json([
            ...$result,
            'conversation' => $conversation->only(['id', 'title']),
        ]);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
