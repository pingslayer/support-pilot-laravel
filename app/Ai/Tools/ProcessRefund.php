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
        return 'Process a refund for a specific order and amount.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        // TODO: Logic for Stripe, PayPal, etc.
        // For example:
        // $paymentGateway->refund($request['order_id'], $request['amount']);

        return "Refund of {$request['amount']} for Order {$request['order_id']} has been initiated successfully.";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->required(),
            'amount' => $schema->string()->required(),
            'reason' => $schema->string()->required(),
        ];
    }
}
