<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, UserController, LeagueController, GameController};
use Illuminate\Support\Facades\Artisan;

Route::get('/seed-database', function () {
    try {
        Artisan::call('db:seed', ['--class' => 'RoleAndModuleSeeder']);
        return response()->json(['message' => 'Database roles and modules seeded successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Database seeding failed', 'message' => $e->getMessage()], 500);
    }
});

Route::get('/migrate', function () {
    try {
        Artisan::call('migrate');
        return response()->json(['message' => 'Database migrated successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Database migrate failed', 'message' => $e->getMessage()], 500);
    }
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

// Protected route example
Route::group(['middleware' => 'auth:api'], function () {
    // Your authenticated API routes here
    Route::get('/validate_token', [UserController::class, 'validate_token']);
    Route::get('/me_user', [UserController::class, 'me_user']);

    Route::group(['prefix' => 'user'], function () {
        Route::post('/update_profile', [UserController::class, 'update_profile']);
        Route::post('/update_password', [UserController::class, 'update_password']);
        Route::post('/update_image', [UserController::class, 'update_image']);
    });


    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'getUsers']);
        Route::get('/league-admins', [UserController::class, 'getLeagueAdmins']);
        Route::get('/card-data', [UserController::class, 'getCardData']);
        Route::post('/update-role/{user_id}', [UserController::class, 'updateRole']);
    });

    Route::group(['prefix' => 'leagues'], function () {
        Route::get('/', [LeagueController::class, 'index']);
        Route::get('/leagues-joined', [LeagueController::class, 'totalLeaguesJoined']);
        Route::post('/store', [LeagueController::class, 'store']);
        Route::post('/join', [LeagueController::class, 'join']);
        Route::patch('/update/{league_id}', [LeagueController::class, 'update']);
        Route::delete('/delete/{league_id}', [LeagueController::class, 'update']);

    });

    Route::group(['prefix' => 'games'], function () {
        Route::get('/', [GameController::class, 'games']);
    });

});

Route::group(['middleware' => 'verify.jwt.jwks'], function () {
    Route::get('/user-details', [UserController::class, 'getUserDetails']);
    Route::post('/getToken', [UserController::class, 'getToken']);
});