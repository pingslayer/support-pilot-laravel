<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetOrderDetails implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieve order details (status, items, total) for a specific order ID.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->description('The ID of the order to retrieve details for.')->required(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $orderId = $request->input('order_id');

        // TODO: Actual DB lookup for the order
        // return Order::where('order_id', $orderId)->first();

        return "Order #{$orderId} was delivered on Mar 25, 2026. Total was $124.99. Items: 1x Widget A, 2x Gadget B.";
    }
}
