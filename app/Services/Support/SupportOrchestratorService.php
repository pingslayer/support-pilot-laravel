<?php

namespace App\Services\Support;

use App\Models\Message;
use App\Models\Ticket;
use App\Ai\Agents\SupportAgent;
use Illuminate\Support\Facades\Log;

class SupportOrchestratorService
{
    /**
     * Process an incoming ticket update via the AI reasoning loop.
     */
    public function handle(Ticket $ticket)
    {
        Log::info("Orchestrating Ticket #{$ticket->id} (Tenant #{$ticket->tenant_id}) using AI SDK.");

        // 1. Resolve or Initialize Conversation
        // We use the ID of the ticket as a unique identifier for the AI conversation.
        $agent = $ticket->ai_conversation_id 
            ? SupportAgent::continue($ticket->ai_conversation_id)
            : new SupportAgent();

        // 2. Gather latest message for the prompt
        $latestMessage = $ticket->messages()->latest()->first();
        if (!$latestMessage || $latestMessage->role !== 'user') {
            Log::warning("No new customer message found for Ticket #{$ticket->id}. Skipping AI processing.");
            return;
        }

        // 3. Invoke the Reasoning Loop
        // The SDK automatically handles Tool calling and persistency.
        $response = $agent->prompt($latestMessage->content);

        // 4. Update Ticket with Conversation ID if it's new
        if (!$ticket->ai_conversation_id) {
            $ticket->update(['ai_conversation_id' => $response->conversationId]);
        }

        // 5. Process Structured Output
        $this->processAiDecision($ticket, $response);

        Log::info("AI Reasoning complete for Ticket #{$ticket->id}. Action: " . $response['action']);
    }

    /**
     * Handle the AI's final decision.
     */
    protected function processAiDecision(Ticket $ticket, $response)
    {
        // 1. Store the AI's thought/reasoning for auditing
        Log::info("AI Thought for Ticket #{$ticket->id}: " . $response['thought']);

        // 2. Handle Action: 'escalate'
        if ($response['action'] === 'escalate') {
            $ticket->update(['status' => 'escalated']);
            
            Message::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'agent',
                'role' => 'assistant',
                'content' => "I'm escalating this to a human specialist to ensure you get the best assistance."
            ]);
            
            return;
        }

        // 3. Handle Action: 'reply'
        if ($response['action'] === 'reply' && !empty($response['reply_message'])) {
            Message::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'agent', // Internal sender_type
                'role' => 'assistant',  // OpenAI compatible role
                'content' => $response['reply_message']
            ]);
            
            // Optionally resolve ticket if the AI thinks it's done (extending schema)
            // if ($response['confidence'] > 95) $ticket->update(['status' => 'closed']);
        }
    }
}
