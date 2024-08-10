<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Team};

class TeamController extends Controller
{
    public function index() {
        $teams = Team::all();
        return response()->json($teams);
    }

    public function teams() {
        $teams = Team::with(['homeGames', 'visitorGames'])->get()->map(function ($team) {
            return [
                'team' => $team,
                'standings' => $team->standings(),
            ];
        });

        return response()->json($teams);
    }
    
}
