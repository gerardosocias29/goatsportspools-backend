<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, AuctionController, AuctionItemController, AuctionItemBidController, UserController, LeagueController, GameController, BetController, TeamController, ContactUsController, SquaresPoolController, SquaresPlayerController, GameRewardTypeController, CreditRequestController, SquaresAdminApplicationController, PlayoffAdminApplicationController, PlayoffPoolController, PlayoffBracketController, PlayoffAdminController, BannerController, SettingsController, DashboardController, PayoutController};
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

    // Dashboard Routes
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/winnings', [DashboardController::class, 'winnings']);
        Route::get('/activity', [DashboardController::class, 'activity']);
        Route::get('/active-pools', [DashboardController::class, 'activePools']);
    });

    Route::group(['prefix' => 'user'], function () {
        Route::post('/update_profile', [UserController::class, 'update_profile']);
        Route::post('/update_password', [UserController::class, 'update_password']);
        Route::post('/update_image', [UserController::class, 'update_image']);
        Route::get('/payment-method', [PayoutController::class, 'getPaymentMethod']);
        Route::put('/payment-method', [PayoutController::class, 'updatePaymentMethod']);
        Route::get('/winnings', [PayoutController::class, 'getUserWinnings']);
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
        Route::put('/{id}/scores', [GameController::class, 'updateScores']);
        Route::get('/recent', [GameController::class, 'getDoneGames']);
        Route::get('/manage', [GameController::class, 'getGames']);
        Route::post('/import', [GameController::class, 'import']);
        Route::delete('/{id}', [GameController::class, 'destroy']);
    });

    Route::group(['prefix' => 'bets'], function () {
        Route::get('/get/{type}', [BetController::class, 'index']);
        Route::post('/wager', [BetController::class, 'store']);
        Route::get('/amount-at-risks', [BetController::class, 'totalAtRisk']);
        Route::get('/get-one/{user_id}', [BetController::class, 'getOne']);
    });

    Route::group(['prefix' => 'teams'], function () {
        Route::get('/', [TeamController::class, 'index']); // Get all teams (supports ?league=NFL filter)
        Route::get('/all', [TeamController::class, 'teams']); // Get teams with standings
        Route::post('/', [TeamController::class, 'store']); // Create team (admin only)
        Route::post('/{id}', [TeamController::class, 'update']); // Update team (admin only)
        Route::delete('/{id}', [TeamController::class, 'destroy']); // Delete team (superadmin only)
    });

    Route::group(['prefix' => 'ncaa_teams'], function () {
        Route::get('/', [TeamController::class, 'ncaaIndex']);
    });

    Route::group(['prefix' => 'auctions'], function () {
        // Static routes FIRST (no dynamic parameters)
        Route::get('/', [AuctionController::class, 'getAuctions']);
        Route::get('/all', [AuctionController::class, 'all']);
        Route::get('/upcoming', [AuctionController::class, 'getUpcomingAuctions']);
        Route::get('/live', [AuctionController::class, 'getLiveAuction']);
        Route::get('/my-items', [AuctionController::class, 'getUserAuctionedItems']);
        Route::post('/create', [AuctionController::class, 'create']);
        Route::post('/remove-bid', [AuctionItemBidController::class, 'removeBid']);

        // Single dynamic parameter routes
        Route::get('/{auctionId}/my-balance', [AuctionController::class, 'myBalance']);
        Route::get('/{auctionId}/get-by-id', [AuctionController::class, 'getAuctionsById']);
        Route::get('/{auctionId}/join', [AuctionController::class, 'auctionJoin']);
        Route::get('/{auctionId}/members', [AuctionController::class, 'auctionMembers']);
        Route::get('/{auctionId}/users', [AuctionController::class, 'auctionUsers']);
        Route::get('/{auctionId}/start', [AuctionController::class, 'startAuction']);
        Route::get('/{auctionId}/end', [AuctionController::class, 'endAuction']);
        Route::get('/{auctionId}/cancel', [AuctionController::class, 'cancelAuction']);
        Route::post('/{auctionId}/set-stream-url', [AuctionController::class, 'setStreamUrl']);
        Route::post('/{auctionId}/set-amounts', [AuctionController::class, 'setAmounts']);
        Route::post('/{auctionId}/brackets', [AuctionItemController::class, 'storeBracket']);

        // Two dynamic parameter routes (last - most specific path segments)
        Route::get('/{auction_id}/{item_id}/set-active-item', [AuctionController::class, 'setActiveItem']);
        Route::get('/{auction_id}/{item_id}/get-active-item', [AuctionController::class, 'getActiveItem']);
        Route::post('/{auction_id}/{item_id}/end-active-item', [AuctionController::class, 'end']);
        Route::post('/{auction_id}/{item_id}/bid', [AuctionItemBidController::class, 'placeBid']);
    });

    // App Settings Routes
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingsController::class, 'index']); // Get all settings (superadmin)
        Route::get('/{key}', [SettingsController::class, 'get']); // Get a setting
        Route::post('/{key}', [SettingsController::class, 'update']); // Update a setting
        Route::post('/{key}/toggle', [SettingsController::class, 'toggle']); // Toggle a boolean setting
    });

    // Game Reward Types Routes
    Route::group(['prefix' => 'game-reward-types'], function () {
        Route::get('/', [GameRewardTypeController::class, 'index']); // Get all reward types
        Route::get('/{id}', [GameRewardTypeController::class, 'show']); // Get single reward type
    });

    // Squares Pools Routes (Authenticated)
    Route::group(['prefix' => 'squares-pools'], function () {
        // Static routes MUST come before dynamic {id} routes
        Route::get('/', [SquaresPoolController::class, 'index']); // Get all pools
        Route::post('/', [SquaresPoolController::class, 'store']); // Create pool
        Route::post('/join', [SquaresPlayerController::class, 'joinPool']); // Join pool with number + password
        Route::get('/my-joined', [SquaresPlayerController::class, 'getMyJoinedPools']); // Get my joined pools

        // Dynamic routes with {id} parameter
        Route::get('/{id}', [SquaresPoolController::class, 'show']); // Get single pool
        Route::post('/{id}/assign-numbers', [SquaresPoolController::class, 'assignNumbersRandom']); // Random number assignment (admin trigger)
        Route::post('/{id}/assign-numbers-ascending', [SquaresPoolController::class, 'assignNumbersAscending']); // Ascending number assignment (0-9 in order)
        Route::post('/{id}/assign-numbers-manual', [SquaresPoolController::class, 'assignNumbersManual']); // Manual number assignment
        Route::post('/{id}/close', [SquaresPoolController::class, 'closePool']); // Close pool
        Route::post('/{id}/reopen', [SquaresPoolController::class, 'reopenPool']); // Reopen pool
        Route::patch('/{id}/settings', [SquaresPoolController::class, 'updateSettings']); // Update pool settings
        Route::put('/{id}/password', [SquaresPoolController::class, 'updatePassword']); // Update pool password (superadmin or pool owner)
        Route::delete('/{id}', [SquaresPoolController::class, 'destroy']); // Delete pool

        // Winner calculation routes
        Route::post('/{id}/calculate-winners', [SquaresPoolController::class, 'calculateWinners']); // Calculate winners for specific quarter
        Route::post('/{id}/calculate-all-winners', [SquaresPoolController::class, 'calculateAllWinners']); // Calculate all winners
        Route::get('/{id}/winners', [SquaresPoolController::class, 'getWinners']); // Get winners
        Route::get('/{id}/players', [SquaresPoolController::class, 'getPlayers']); // Get joined players for a pool

        // Player routes with {poolId} parameter
        Route::get('/{poolId}/squares', [SquaresPlayerController::class, 'getSquares']); // Get all squares
        Route::get('/{poolId}/my-squares', [SquaresPlayerController::class, 'getMySquares']); // Get my squares
        Route::post('/{poolId}/claim-square', [SquaresPlayerController::class, 'claimSquare']); // Claim a square
        Route::post('/{poolId}/release-square', [SquaresPlayerController::class, 'releaseSquare']); // Release a square
        Route::post('/{poolId}/add-credits', [SquaresPlayerController::class, 'addCredits']); // Add credits (admin only)
        Route::post('/{poolId}/leave', [SquaresPlayerController::class, 'leavePool']); // Leave pool (before close/number assignment)
    });

    // Credit Request Routes
    Route::group(['prefix' => 'credit-requests'], function () {
        // Player → Commissioner credit requests
        Route::post('/pools/{poolId}/request', [CreditRequestController::class, 'requestCreditsFromCommissioner']); // Request credits from commissioner
        Route::get('/pools/{poolId}', [CreditRequestController::class, 'getPoolRequests']); // Get requests for a specific pool (commissioner only)
        Route::get('/commissioner', [CreditRequestController::class, 'getCommissionerRequests']); // Get all requests where I'm commissioner
        Route::patch('/{requestId}', [CreditRequestController::class, 'updateCreditRequest']); // Approve/deny request

        // Square Admin → Superadmin credit requests
        Route::post('/admin/request', [CreditRequestController::class, 'requestCreditsFromSuperadmin']); // Request credits from superadmin
        Route::get('/admin', [CreditRequestController::class, 'getSuperadminRequests']); // Get all admin requests (superadmin only)
        Route::patch('/admin/{requestId}', [CreditRequestController::class, 'updateAdminCreditRequest']); // Approve/deny admin request (superadmin only)

        // Get my own requests
        Route::get('/my-requests', [CreditRequestController::class, 'getMyRequests']); // Get my credit requests
    });

    // Squares Admin Applications Routes
    Route::group(['prefix' => 'squares-admin-applications'], function () {
        Route::get('/my-status', [SquaresAdminApplicationController::class, 'myStatus']); // Get current user's application status
        Route::post('/', [SquaresAdminApplicationController::class, 'store']); // Submit new application
        Route::get('/', [SquaresAdminApplicationController::class, 'index']); // List all applications (Superadmin only)
        Route::patch('/{id}', [SquaresAdminApplicationController::class, 'update']); // Update application status (Superadmin only)
    });

    // Playoff Admin Applications Routes
    Route::group(['prefix' => 'playoff-admin-applications'], function () {
        Route::get('/my-status', [PlayoffAdminApplicationController::class, 'myStatus']); // Get current user's playoff application status
        Route::post('/', [PlayoffAdminApplicationController::class, 'store']); // Submit new playoff application
        Route::get('/', [PlayoffAdminApplicationController::class, 'index']); // List all playoff applications (Superadmin only)
        Route::patch('/{id}', [PlayoffAdminApplicationController::class, 'update']); // Update playoff application status (Superadmin only)
    });

    // Playoff Pools Routes
    Route::group(['prefix' => 'playoff-pools'], function () {
        // Static routes BEFORE dynamic
        Route::get('/list', [PlayoffPoolController::class, 'index']); // List user's pools (superadmin sees all)
        Route::post('/join', [PlayoffPoolController::class, 'join']); // Join pool
        Route::get('/{id}/standings', [PlayoffPoolController::class, 'standings']); // Pool standings
        Route::get('/{id}', [PlayoffPoolController::class, 'show']); // Get pool by ID or pool_number

        // Brackets (nested under pool)
        Route::get('/{id}/brackets', [PlayoffBracketController::class, 'index']); // List user's brackets
        Route::post('/{id}/brackets', [PlayoffBracketController::class, 'store']); // Create bracket
        Route::get('/{id}/brackets/{bracketId}', [PlayoffBracketController::class, 'show']); // Show bracket + picks
        Route::put('/{id}/brackets/{bracketId}', [PlayoffBracketController::class, 'update']); // Rename bracket
        Route::post('/{id}/brackets/{bracketId}/picks', [PlayoffBracketController::class, 'savePicks']); // Upsert picks
        Route::post('/{id}/brackets/{bracketId}/finalize', [PlayoffBracketController::class, 'finalize']); // Finalize bracket
    });

    // Admin Playoff Routes (Superadmin only, enforced in controller)
    Route::group(['prefix' => 'admin/playoffs'], function () {
        // Static routes BEFORE dynamic
        Route::post('/create', [PlayoffAdminController::class, 'createPlayoff']); // Create playoff year
        Route::post('/pools', [PlayoffAdminController::class, 'createPool']); // Create playoff pool
        Route::get('/nba-teams', [PlayoffAdminController::class, 'listNbaTeams']); // List NBA teams for selection
        Route::get('/', [PlayoffAdminController::class, 'listPlayoffs']); // List all playoffs

        // Pool management by pool_number
        Route::post('/pools/{poolNumber}/lock', [PlayoffAdminController::class, 'lockPool']);
        Route::post('/pools/{poolNumber}/recalculate', [PlayoffAdminController::class, 'recalculatePool']);
        Route::patch('/pools/{poolNumber}', [PlayoffAdminController::class, 'updatePool']);
        Route::delete('/pools/{poolNumber}', [PlayoffAdminController::class, 'deletePool']);
        Route::get('/pools/{poolNumber}/brackets', [PlayoffAdminController::class, 'listPoolBrackets']);
        Route::patch('/brackets/{bracketId}/paid', [PlayoffAdminController::class, 'toggleBracketPaid']);

        // Dynamic routes — more specific sub-paths first
        Route::post('/{id}/teams', [PlayoffAdminController::class, 'saveTeams']); // Save teams for a playoff
        Route::get('/{id}/summary', [PlayoffAdminController::class, 'roundSummary']); // Round summary
        Route::post('/{id}/results/bulk', [PlayoffAdminController::class, 'bulkUpdateResults']); // Enter round results
        Route::post('/{id}/results/clear', [PlayoffAdminController::class, 'clearResult']); // Clear a result
        Route::post('/{id}/results', [PlayoffAdminController::class, 'updateResult']); // Enter single result
        Route::post('/{id}/score-all', [PlayoffAdminController::class, 'scoreAll']); // Rescore all rounds
        Route::post('/{id}/score', [PlayoffAdminController::class, 'scoreRound']); // Score a round
        Route::get('/{id}/global-standings', [PlayoffAdminController::class, 'globalStandings']); // Global standings across all pools
        Route::get('/{id}/standings/{poolId}', [PlayoffAdminController::class, 'poolStandings']); // Pool standings
        Route::get('/{id}', [PlayoffAdminController::class, 'showPlayoff']); // Show single playoff
        Route::patch('/{id}', [PlayoffAdminController::class, 'updatePlayoff']); // Update playoff (name/year/status)
        Route::delete('/{id}', [PlayoffAdminController::class, 'deletePlayoff']); // Archive playoff
    });

    // Admin Payout Routes (Superadmin only)
    Route::group(['prefix' => 'admin/payouts'], function () {
        Route::get('/', [PayoutController::class, 'adminIndex']);
        Route::post('/{winnerId}/mark-paid', [PayoutController::class, 'markAsPaid']);
    });

    // Banner Management Routes (Admin only)
    Route::group(['prefix' => 'banners'], function () {
        Route::get('/manage', [BannerController::class, 'manage']); // Get all banners for admin
        Route::post('/', [BannerController::class, 'store']); // Create banner
        Route::get('/{id}', [BannerController::class, 'show']); // Get single banner
        Route::put('/{id}', [BannerController::class, 'update']); // Update banner
        Route::delete('/{id}', [BannerController::class, 'destroy']); // Delete banner
        Route::patch('/{id}/toggle-status', [BannerController::class, 'toggleStatus']); // Toggle status
    });
});

// Squares Pools Public Routes (No Auth Required)
Route::get('/squares-pools/by-number/{poolNumber}', [SquaresPlayerController::class, 'getPoolByNumber']); // Get pool by number (public)

// Playoff Pools Public Routes (No Auth Required)
Route::get('/playoff-pools/by-number/{poolNumber}', [PlayoffPoolController::class, 'lookupByNumber']); // Lookup pool by number (public)

// Banners Public Route (No Auth Required)
Route::get('/banners', [BannerController::class, 'index']); // Get active banners for display

Route::group(['middleware' => 'verify.jwt.jwks'], function () {
    Route::get('/user-details', [UserController::class, 'getUserDetails']);
    Route::post('/getToken', [UserController::class, 'getToken']);
});

Route::post('/bid', function (Request $request) {
    event(new NewBid($request->username, $request->amount));
    return response()->json(['status' => 'Bid placed!']);
});
