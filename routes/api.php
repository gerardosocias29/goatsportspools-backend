<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, AuctionController, AuctionItemController, AuctionItemBidController, UserController, LeagueController, GameController, BetController, TeamController, ContactUsController};
use Illuminate\Support\Facades\Artisan;
use App\Events\NewBid;
use App\CustomLibraries\PushNotification;
use App\Models\{AuctionItem};

Route::post('/contact-us/send', [ContactUsController::class, 'send']);

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

Route::get('/d', [UserController::class, 'getData']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

Route::post('/auctions/{auctionId}/{userId}/leave', [AuctionController::class, 'auctionAway']);

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
        Route::get('/all', [UserController::class, 'getAllUsers']);
        Route::get('/league-admins', [UserController::class, 'getLeagueAdmins']);
        Route::get('/card-data', [UserController::class, 'getCardData']);
        Route::post('/update-role/{user_id}', [UserController::class, 'updateRole']);
    });

    Route::group(['prefix' => 'leagues'], function () {
        Route::get('/', [LeagueController::class, 'index']);
        Route::get('/active-leagues', [LeagueController::class, 'getLeagues']);
        Route::get('/get/{id}', [LeagueController::class, 'getLeagueById']);
        Route::get('/leagues-joined', [LeagueController::class, 'totalLeaguesJoined']);
        Route::get('/leagues-created', [LeagueController::class, 'getLeaguesCreatedCount']);
        Route::get('/joined', [LeagueController::class, 'joinedLeagues']);
        Route::post('/store', [LeagueController::class, 'store']);
        Route::post('/join', [LeagueController::class, 'join']);
        Route::post('/update/{league_id}', [LeagueController::class, 'update']);
        Route::post('/rebuy', [LeagueController::class, 'rebuy']);
        Route::post('/buyin', [LeagueController::class, 'buyin']);
        Route::delete('/delete/{league_id}', [LeagueController::class, 'update']);
        Route::get('/default', [LeagueController::class, 'getDefaultLeague']);

    });

    Route::group(['prefix' => 'games'], function () {
        Route::get('/', [GameController::class, 'games']);
        Route::get('/weekly', [GameController::class, 'weeklyGames']);
        Route::post('/announce-winner', [GameController::class, 'announceWinner']);
        Route::post('/create', [GameController::class, 'create']);
        Route::post('/update/{id}', [GameController::class, 'update']);
        Route::get('/recent', [GameController::class, 'getDoneGames']);
        Route::get('/manage', [GameController::class, 'getGames']);
    });

    Route::group(['prefix' => 'bets'], function () {
        Route::get('/get/{type}', [BetController::class, 'index']);
        Route::post('/wager', [BetController::class, 'store']);
        Route::get('/amount-at-risks', [BetController::class, 'totalAtRisk']);
        Route::get('/get-one/{user_id}', [BetController::class, 'getOne']);
    });

    Route::group(['prefix' => 'teams'], function () {
        Route::get('/', [TeamController::class, 'index']);
        Route::get('/all', [TeamController::class, 'teams']);
    });

    Route::group(['prefix' => 'ncaa_teams'], function () {
        Route::get('/', [TeamController::class, 'ncaaIndex']);
    });

    Route::group(['prefix' => 'auctions'], function () {
        Route::get('/', [AuctionController::class, 'getAuctions']);
        Route::get('/all', [AuctionController::class, 'all']);
        Route::get('/{auctionId}/get-by-id', [AuctionController::class, 'getAuctionsById']);
        Route::get('/{auctionId}/join', [AuctionController::class, 'auctionJoin']);
        Route::get('/{auctionId}/members', [AuctionController::class, 'auctionMembers']);
        Route::get('/{auctionId}/users', [AuctionController::class, 'auctionUsers']);
        
        Route::post('/create', [AuctionController::class, 'create']);
        Route::post('/{auction_id}/set-stream-url', [AuctionController::class, 'setStreamUrl']);
        Route::post('/{auction_id}/set-amounts', [AuctionController::class, 'setAmounts']);
        Route::post('/{auction_id}/brackets', [AuctionItemController::class, 'storeBracket']);

        Route::post('/{auction_id}/{item_id}/end-active-item', [AuctionController::class, 'end']);
        Route::get('/{auction_id}/{item_id}/set-active-item', [AuctionController::class, 'setActiveItem']);
        // Route::get('/{auction_id}/{ncaa_team_id}/get-auction-details', [AuctionController::class, 'getAuctionDetails']);
        Route::get('/{auction_id}/{item_id}/get-active-item', [AuctionController::class, 'getActiveItem']);
        
        Route::get('/{auction_id}/end', [AuctionController::class, 'endAuction']);
        Route::get('/{auction_id}/cancel', [AuctionController::class, 'cancelAuction']);
        Route::get('/upcoming', [AuctionController::class, 'getUpcomingAuctions']);
        Route::get('/live', [AuctionController::class, 'getLiveAuction']);
        Route::get('/my-items', [AuctionController::class, 'getUserAuctionedItems']);

        Route::post('/remove-bid', [AuctionItemBidController::class, 'removeBid']);
        Route::post('/{auction_id}/{item_id}/bid', [AuctionItemBidController::class, 'placeBid']);
    });
});

Route::group(['middleware' => 'verify.jwt.jwks'], function () {
    Route::get('/user-details', [UserController::class, 'getUserDetails']);
    Route::post('/getToken', [UserController::class, 'getToken']);
});

Route::post('/bid', function (Request $request) {
    event(new NewBid($request->username, $request->amount));
    return response()->json(['status' => 'Bid placed!']);
});