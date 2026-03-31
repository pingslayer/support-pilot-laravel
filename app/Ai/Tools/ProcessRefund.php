<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ProcessRefund implements Tool
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
        $orderId = (string) $request['order_id'];
        $amount = (float) $request['amount'];
        $reason = (string) $request['reason'];

        $tenant = \App\Models\Tenant::find($this->tenantId);
        $integration = new \App\Services\Integrations\ExternalIntegrationService($tenant);

        $response = $integration->processRefund($orderId, $amount, $reason);

        if (isset($response['error'])) {
            return "Error: " . $response['error'];
        }

        return "Successfully processed refund: " . json_encode($response);
    }
}
