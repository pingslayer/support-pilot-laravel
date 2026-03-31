<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Public Onboarding & Auth
 */
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

/**
 * Protected SaaS Management
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('tenant');
    });
});

/**
 * External Webhooks
 */
Route::post('/webhooks/incoming-email', [WebhookController::class, 'handleEmail']);
