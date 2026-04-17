<?php

namespace App\Http\Controllers;

use App\Services\ChatbotAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private const HISTORY_SESSION_KEY = 'chatbot_history';
    private const LAST_MESSAGE_AT_SESSION_KEY = 'chatbot_last_message_at';
    private const PENDING_INPUT_SESSION_KEY = 'chatbot_pending_input';
    private const COOLDOWN_SECONDS = 3;

    public function context(ChatbotAccessService $chatbotAccessService): JsonResponse
    {
        if (! session('logged_in')) {
            return response()->json([
                'message' => 'Sesi login tidak ditemukan.',
            ], 401);
        }

        return response()->json([
            'message' => 'Konteks chatbot berhasil dimuat.',
            'data' => array_merge(
                $chatbotAccessService->buildContext((array) session('user')),
                [
                    'history_count' => count((array) session(self::HISTORY_SESSION_KEY, [])),
                    'pending_input' => session(self::PENDING_INPUT_SESSION_KEY),
                ]
            ),
        ]);
    }

    public function ask(Request $request, ChatbotAccessService $chatbotAccessService): JsonResponse
    {
        if (! session('logged_in')) {
            return response()->json([
                'message' => 'Sesi login tidak ditemukan.',
            ], 401);
        }

        $lastMessageAt = (int) session(self::LAST_MESSAGE_AT_SESSION_KEY, 0);
        $now = time();
        $elapsed = $now - $lastMessageAt;

        if ($lastMessageAt > 0 && $elapsed < self::COOLDOWN_SECONDS) {
            $retryAfter = self::COOLDOWN_SECONDS - $elapsed;

            return response()->json([
                'message' => 'Mohon tunggu sebentar sebelum mengirim pesan berikutnya.',
                'data' => [
                    'cooldown' => true,
                    'retry_after' => $retryAfter,
                ],
            ], 429);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'option_id' => ['nullable', 'string', 'max:120'],
        ], [
            'message.required' => 'Pesan chatbot wajib diisi.',
        ]);

        $history = (array) session(self::HISTORY_SESSION_KEY, []);
        $pendingInput = (array) session(self::PENDING_INPUT_SESSION_KEY, []);
        $optionId = $validated['option_id'] ?? null;

        if (filled($optionId)) {
            session()->forget(self::PENDING_INPUT_SESSION_KEY);
        }

        $responseData = $pendingInput !== [] && blank($optionId)
            ? $chatbotAccessService->submitPendingInput((array) session('user'), $validated['message'], $pendingInput)
            : $chatbotAccessService->respond(
                (array) session('user'),
                $validated['message'],
                $history,
                $optionId
            );

        $updatedHistory = array_slice(array_merge($history, [
            ['role' => 'user', 'message' => $validated['message']],
            ['role' => 'assistant', 'message' => (string) ($responseData['message'] ?? '')],
        ]), -10);

        if (! empty($responseData['pending_input'])) {
            session([self::PENDING_INPUT_SESSION_KEY => $responseData['pending_input']]);
        } else {
            session()->forget(self::PENDING_INPUT_SESSION_KEY);
        }

        session([
            self::HISTORY_SESSION_KEY => $updatedHistory,
            self::LAST_MESSAGE_AT_SESSION_KEY => $now,
        ]);

        return response()->json([
            'message' => 'Permintaan chatbot diproses.',
            'data' => $responseData,
        ]);
    }

    public function reset(): JsonResponse
    {
        if (! session('logged_in')) {
            return response()->json([
                'message' => 'Sesi login tidak ditemukan.',
            ], 401);
        }

        session()->forget(self::HISTORY_SESSION_KEY);
        session()->forget(self::LAST_MESSAGE_AT_SESSION_KEY);
        session()->forget(self::PENDING_INPUT_SESSION_KEY);

        return response()->json([
            'message' => 'Percakapan chatbot berhasil direset.',
        ]);
    }
}
