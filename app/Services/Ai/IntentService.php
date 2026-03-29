<?php

namespace App\Services\Ai;

use function Laravel\Ai\agent;
use Illuminate\Support\Facades\Log;

class IntentService
{
    /**
     * Classify the intent of a customer message.
     */
    public function classify(string $text): string
    {
        try {
            // Check if API key is still the placeholder
            $apiKey = config('ai.providers.openai.key');
            if ($apiKey === 'your-api-key-here' || empty($apiKey)) {
                Log::warning('OpenAI API Key not set. Defaulting intent to "inquiry".');
                return 'inquiry';
            }

            // Using the official Laravel AI SDK agent helper
            $response = agent(
                instructions: 'You are a support agent classifier. Classify the message intent as one of: refund, complaint, inquiry. Respond with ONLY the single word intent.'
            )->prompt($text);

            return trim(strtolower($response->text));
        } catch (\Exception $e) {
            Log::error('AI Intent Classification failed: ' . $e->getMessage());
            return 'inquiry';
        }
    }
}
