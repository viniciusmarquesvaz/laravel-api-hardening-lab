<?php

use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [AuthTokenController::class, 'store'])
    ->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::delete('/tokens/current', [AuthTokenController::class, 'destroy']);
    Route::apiResource('tickets', TicketController::class);
});
