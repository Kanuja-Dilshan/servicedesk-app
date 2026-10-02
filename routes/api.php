<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', fn (Request $r) => $r->user()->only('id', 'name', 'email', 'role'));

    Route::apiResource('tickets', TicketController::class)->except('destroy');
    Route::post('tickets/{ticket}/comments', [TicketController::class, 'comment']);
});