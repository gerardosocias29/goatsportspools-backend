<?php

namespace App\Http\Controllers;

use App\Models\GameRewardType;
use Illuminate\Http\Request;

class GameRewardTypeController extends Controller
{
    /**
     * Get all reward types
     * GET /api/game-reward-types
     */
    public function index()
    {
        $rewardTypes = GameRewardType::all();

        return response()->json([
            'status' => true,
            'data' => $rewardTypes
        ]);
    }

    /**
     * Get single reward type
     * GET /api/game-reward-types/{id}
     */
    public function show($id)
    {
        $rewardType = GameRewardType::find($id);

        if (!$rewardType) {
            return response()->json([
                'status' => false,
                'message' => 'Reward type not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $rewardType
        ]);
    }
}
