<?php

namespace App\Services\Support;

use App\Models\Message;
use App\Models\Ticket;
use App\Ai\Agents\SupportAgent;
use Illuminate\Support\Facades\Log;

class SupportOrchestratorService
{
    /**
     * Process an incoming ticket update.
     */
    public function handle(Ticket $ticket)
    {
        Log::info("Orchestrating Ticket #{$ticket->id} for Tenant #{$ticket->tenant_id}");

        // 1. Gather Context
        // This includes customer history, past messages, and (future) RAG search results.
        $context = $this->gatherContext($ticket);

        // 2. Invoke the "Brain"
        // TODO: This is where you would call your chosen Laravel AI Agent or direct LLM integration.
        // For example:
        // $agent = new \App\Ai\Agents\SupportAgent();
        // $response = $agent->prompt($context);
        
        // 3. Handle Decisions/Tools
        // If the decision is to reply:
        // $this->dispatchReply($ticket, "Your answer here");
        
        // If the decision is to escalate:
        // $ticket->update(['status' => 'escalated']);

        Log::info("Ticket #{$ticket->id} processing logic complete (Skeleton mode).");
    }

    /**
     * Build the context for the AI reasoning loop.
     */
    protected function gatherContext(Ticket $ticket): array
    {
        return [
            'ticket_id' => $ticket->id,
            'subject' => $ticket->subject,
            'customer' => $ticket->customer->only(['name', 'email']),
            'history' => $ticket->messages()->latest()->take(5)->get()->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
        ];
    }
}
