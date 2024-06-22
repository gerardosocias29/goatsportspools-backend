<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, UserController};

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

// Protected route example
Route::group(['middleware' => 'auth:api'], function () {
    // Your authenticated API routes here
    Route::get('/validate_token',   [UserController::class, 'validate_token']);
    Route::get('/me_user',          [UserController::class, 'me_user']);
    
});

