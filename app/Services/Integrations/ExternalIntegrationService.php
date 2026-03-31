<?php

namespace App\Services\Integrations;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalIntegrationService
{
    /**
     * Create a new service instance.
     */
    public function __construct(protected Tenant $tenant) {}

    /**
     * Fetch order details from the tenant's external store.
     */
    public function fetchOrder(string $orderId): array
    {
        if (!$this->tenant->external_api_url) {
            return ['error' => 'External Store integration is not configured for this tenant.'];
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->tenant->external_api_key,
                'Accept' => 'application/json',
            ])->get("{$this->tenant->external_api_url}/orders/{$orderId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("External API Error for Tenant #{$this->tenant->id}: " . $response->body());
            return ['error' => 'Could not find order in external store.'];

        } catch (\Exception $e) {
            Log::error("Integration failure for Tenant #{$this->tenant->id}: " . $e->getMessage());
            return ['error' => 'Failed to connect to the external store.'];
        }
    }

    /**
     * Process a refund in the external store.
     */
    public function processRefund(string $orderId, float $amount, string $reason): array
    {
        if (!$this->tenant->external_api_url) {
            return ['error' => 'External Store integration is not configured for this tenant.'];
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->tenant->external_api_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->tenant->external_api_url}/orders/{$orderId}/refund", [
                'amount' => $amount,
                'reason' => $reason,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'The external store rejected the refund request: ' . ($response->json('message') ?? 'Unknown error')];

        } catch (\Exception $e) {
            return ['error' => 'Failed to connect to the external store to process refund.'];
        }
    }
}
