<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RateLimitRequests;
use App\Http\Middleware\ValidateHoneypot;

Route::middleware([
    RateLimitRequests::class,
    ValidateHoneypot::class,
])->group(function () {
    Route::post('/segnalazioni', [\App\Http\Controllers\Api\SegnalazioneController::class, 'store']);
});

Route::get('/segnalazioni', [\App\Http\Controllers\Api\SegnalazioneController::class, 'list']);
Route::put('/segnalazioni/{id}/status', [\App\Http\Controllers\Api\SegnalazioneController::class, 'updateStatus']);
