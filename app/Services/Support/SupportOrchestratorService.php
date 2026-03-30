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
    public function handle(\App\Models\Message $message)
    {
        $ticket = $message->ticket;

        Log::info("Orchestrating Ticket #{$ticket->id} (Tenant #{$ticket->tenant_id}) using AI SDK.");

        // 1. Resolve or Initialize Conversation
        // We use the Ticket itself as the "User" context for the AI conversation store.
        $agent = (new SupportAgent());

        if ($ticket->ai_conversation_id) {
            $agent->continue($ticket->ai_conversation_id, as: $ticket);
        } else {
            $agent->forUser($ticket);
        }

        // Inject Tenant context for local RAG search
        $agent->tenantId = $ticket->tenant_id;

        // 4. Prompt the Brain
        $response = $agent->prompt($message->content);

        // 5. Update Ticket with Conversation ID if it's new
        if (!$ticket->ai_conversation_id && $response->conversationId) {
            $ticket->update(['ai_conversation_id' => $response->conversationId]);
        }

        // 6. Process the structured response
        // $response is an AgentResponse object, not an array.
        $data = json_decode($response->text, true);

        // 5. Process Structured Output
        $this->processAiDecision($ticket, $data);

        Log::info("AI Reasoning complete for Ticket #{$ticket->id}. Action: " . $data['action']);
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
