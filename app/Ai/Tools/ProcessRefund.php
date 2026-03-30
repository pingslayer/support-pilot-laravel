<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ProcessRefund implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Process a refund request for a specific order.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->description('The ID of the order to refund.')->required(),
            'amount' => $schema->string()->description('The total refund amount to process.')->required(),
            'reason' => $schema->string()->description('Reason for the refund.')->required(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $orderId = $request->input('order_id');
        $amount = $request->input('amount');
        $reason = $request->input('reason');

        // TODO: Logic for Stripe, PayPal, etc.
        // return Payment::refund($orderId, $amount, $reason);

        return "Successfully initiated a refund of {$amount} for Order #{$orderId}. Reason: {$reason}.";
    }
}
