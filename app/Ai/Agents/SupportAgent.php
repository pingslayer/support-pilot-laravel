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
 * The SupportAgent is the "Brain" of the ticket processing loop.
 */
class SupportAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return "You are an intelligent customer support agent for a multivendor SaaS platform.
        Your goal is to help customers with their queries regarding orders, refunds, and technical issues.
        
        Behavioral Rules:
        1. Always be polite and professional.
        2. Use the available tools to fetch real data before providing answers.
        3. If you are unsure of an answer, or your confidence is low (below 80), use the EscalateToHuman tool.
        4. Always provide a final answer in the structured output format.";
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
            'reply_message' => $schema->string()->description('The message to send to the customer if action is reply.')->required(),
        ];
    }
}
