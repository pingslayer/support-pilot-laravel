<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetOrderDetails implements Tool
{
    /**
     * Create a new tool instance.
     */
    public function __construct(protected int $tenantId) {}

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
        $orderId = (string) $request['order_id'];

        $tenant = \App\Models\Tenant::find($this->tenantId);
        $integration = new \App\Services\Integrations\ExternalIntegrationService($tenant);
        
        $response = $integration->fetchOrder($orderId);

        if (isset($response['error'])) {
            return "Error: " . $response['error'];
        }

        return "Order Information: " . json_encode($response);
    }
}
