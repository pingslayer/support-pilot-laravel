<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantSettingsController extends Controller
{
    /**
     * Display the authenticated user's tenant settings.
     */
    public function show(Request $request)
    {
        return response()->json($request->user()->tenant);
    }

    /**
     * Update the tenant settings (External API integration).
     */
    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'external_api_url' => ['nullable', 'url', 'max:255'],
            'external_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant->update($validated);

        return response()->json([
            'message' => 'Integration settings updated successfully.',
            'tenant' => $tenant,
        ]);
    }
}
