<?php

namespace App\Http\Controllers;

use App\Services\DiscordInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscordInteractionController extends Controller
{
    public function __construct(
        protected DiscordInteractionService $interactionService
    ) {}

    /**
     * Handle incoming Discord interactions.
     */
    public function handle(Request $request): JsonResponse
    {
        // Get signature headers
        $signature = $request->header('X-Signature-Ed25519');
        $timestamp = $request->header('X-Signature-Timestamp');
        $body = $request->getContent();

        // For debugging
        Log::info('Discord interaction attempt', [
            'method' => $request->method(),
            'has_signature' => !empty($signature),
            'has_timestamp' => !empty($timestamp),
            'body_length' => strlen($body),
        ]);

        // Verify signature (required for all Discord interactions)
        if (!$signature || !$timestamp) {
            Log::warning('Discord interaction missing signature headers');
            return response()->json(['error' => 'Invalid request'], 401);
        }

        if (!$this->interactionService->verifySignature($signature, $timestamp, $body)) {
            Log::warning('Discord interaction signature verification failed', [
                'signature' => substr($signature, 0, 20) . '...',
                'timestamp' => $timestamp,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Parse interaction
        $interaction = $request->json()->all();

        Log::info('Discord interaction received', [
            'type' => $interaction['type'] ?? 'unknown',
            'command' => $interaction['data']['name'] ?? null,
        ]);

        // Handle interaction
        $response = $this->interactionService->handleInteraction($interaction);

        return response()->json($response);
    }
}
