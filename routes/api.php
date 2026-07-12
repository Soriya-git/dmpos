<?php

use App\Http\Controllers\Api\PrintAgentController;
use App\Http\Middleware\AuthenticatePrintAgent;
use Illuminate\Support\Facades\Route;

Route::prefix('print-agent')
    ->middleware([AuthenticatePrintAgent::class, 'throttle:print-agent'])
    ->group(function (): void {
        Route::post('/claim', [PrintAgentController::class, 'claim']);
        Route::post('/jobs/{printJob}/printed', [PrintAgentController::class, 'printed']);
        Route::post('/jobs/{printJob}/failed', [PrintAgentController::class, 'failed']);
    });
