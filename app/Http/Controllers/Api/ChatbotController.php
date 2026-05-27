<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ChatbotController extends Controller
{
    public function __construct(private ChatbotService $chatbot) {}

    /**
     * POST /api/chatbot/ask
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:10',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:1000',
        ]);

        // Rate limiting: 20 requests per minute per IP
        $key = 'chatbot:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de messages envoyés. Veuillez patienter quelques secondes.',
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $result = $this->chatbot->ask(
            $request->input('message'),
            $request->input('history', [])
        );

        return response()->json($result);
    }
}
