<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Mock External Store API (for local AI simulation testing).
 */
Route::prefix('test/external-store')->group(function () {
    Route::get('orders/{id}', function ($id) {
        return response()->json([
            'id' => $id,
            'status' => 'delivered',
            'amount' => 124.99,
            'currency' => 'USD',
            'items' => [
                ['name' => 'Widget A', 'quantity' => 1],
                ['name' => 'Gadget B', 'quantity' => 2],
            ],
            'customer_email' => 'customer@example.com',
            'created_at' => '2026-03-25T10:00:00Z',
        ]);
    });

    Route::post('orders/{id}/refund', function ($id) {
        return response()->json([
            'success' => true,
            'transaction_id' => 'REF-' . strtoupper(Str::random(10)),
            'message' => 'Refund processed successfully in external system.',
        ]);
    });
});
