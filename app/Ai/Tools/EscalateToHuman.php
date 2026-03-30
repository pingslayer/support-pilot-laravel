<?php

namespace App\Ai\Tools;

use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class EscalateToHuman implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Escalate the current ticket to a human support agent for manual review.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        // TODO: Get the ticket ID from the context or the request if needed.
        // For example:
        // $ticket = Ticket::find($request->ticket_id);
        // $ticket->update(['status' => 'escalated']);

        return 'Escalation successful. The ticket will now be handled by a human.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'reason' => $schema->string()->required(),
        ];
    }
}
