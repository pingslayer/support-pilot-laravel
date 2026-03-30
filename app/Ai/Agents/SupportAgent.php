<?php

namespace App\Ai\Agents;

use App\Ai\Tools\EscalateToHuman;
use App\Ai\Tools\GetOrderDetails;
use App\Ai\Tools\ProcessRefund;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * The consolidated ChatAgent. Simple and developer-friendly.
 */
class SupportAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a friendly customer support agent. Help customers with their orders and queries.';
    }

    /**
     * Get the tools available to the agent.
     */
    public function tools(): iterable
    {
        return [
            new GetOrderDetails,
            new ProcessRefund,
            new EscalateToHuman,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'confidence' => $schema->integer()->min(0)->max(100)->required(),
            'thought' => $schema->string()->required(),
            'action' => $schema->string()->enum(['reply', 'escalate'])->required(),
            'reply_message' => $schema->string(),
        ];
    }
}
