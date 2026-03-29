<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingMessageJob;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleEmail(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string',
            'from_email' => 'required|email',
            'from_name' => 'nullable|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'message_id' => 'required|string', // UUID from email provider
        ]);

        // Verify Tenant
        $tenant = Tenant::where('api_key', $validated['api_key'])->first();

        if (!$tenant) {
            return response()->json(['message' => 'Invalid API Key'], 401);
        }

        // Idempotency: Check if this message was already processed
        $cacheKey = "webhook_msg_" . $validated['message_id'];
        if (Cache::has($cacheKey)) {
            return response()->json(['message' => 'Duplicate message'], 200);
        }

        // Lock it in for 24 hours to prevent reruns
        Cache::put($cacheKey, true, now()->addDay());

        // Dispatch Job
        ProcessIncomingMessageJob::dispatch(
            $tenant->id,
            $validated['from_email'],
            $validated['from_name'] ?? 'Unknown',
            $validated['subject'],
            $validated['body']
        );

        return response()->json(['message' => 'Accepted'], 202);
    }
}
