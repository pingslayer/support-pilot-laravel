<?php

namespace App\Ai\Agents;

use App\Ai\Tools\EscalateToHuman;
use App\Ai\Tools\SendReply;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * The `SupportAgent` is the "Brain" of the ticket processing loop.
 * You can implement your prompt logic and tool associations here.
 */
class SupportAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        // TODO: Define your system instructions here.
        return 'You are a professional support agent. Your goal is to help customers efficiently.';
    }

    /**
     * Get the tools available to the agent.
     */
    public function tools(): iterable
    {
        // TODO: Register your tools here.
        return [
            // new SendReply,
            // new EscalateToHuman,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        // TODO: Define the structured output you want your LLM to follow.
        return [
            'confidence' => $schema->integer()->min(0)->max(100)->required(),
            'thought' => $schema->string()->required(),
            'action' => $schema->string()->enum(['reply', 'escalate', 'needs_more_info'])->required(),
        ];
    }
}
