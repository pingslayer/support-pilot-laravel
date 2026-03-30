<?php

namespace App\Ai\Tools;

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
        return 'Escalate the current ticket to a human support agent.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'reason' => $schema->string()->description('The reason for escalation.')->required(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $reason = $request->input('reason');

        // Logic handled in orchestrator based on action: 'escalate'
        // return $ticket->update(['status' => 'escalated']);

        return "Successfully escalated to a human for the following reason: {$reason}";
    }
}
