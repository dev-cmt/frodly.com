<?php

use App\Http\Controllers\Auth\JwtAuthController;
use App\Http\Controllers\Api\FrodlyController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [JwtAuthController::class, 'register']);
Route::post('/login', [JwtAuthController::class, 'login']);

// Protected routes (JWT required)
Route::middleware('auth:api')->group(function () {
    // Route::post('/check-courier', [FrodlyController::class, 'check']);
    Route::post('/logout', [JwtAuthController::class, 'logout']);
    Route::post('/refresh', [JwtAuthController::class, 'refresh']);
});
Route::post('/token/check-courier', [FrodlyController::class, 'checkManualy']);


Route::post('/check-courier', [FrodlyController::class, 'check'])->middleware('token.valid');