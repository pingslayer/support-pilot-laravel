<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class IntentClassifier implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are a support agent classifier. Analyze the customer message and classify it as one of: refund, complaint, or inquiry. Respond with ONLY the single word classification.';
    }
}
