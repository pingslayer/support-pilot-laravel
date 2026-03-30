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
        return 'Retrieve details for a specific order by its order ID.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        // TODO: Logic to fetch order from your database
        // For example:
        // $order = Order::where('order_number', $request->order_id)->first();
        // return json_encode($order);

        return "Mocked Order Details: Status 'Delivered', Total '$99.00', Items: ['Gadget A', 'Widget B']";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->required(),
        ];
    }
}
