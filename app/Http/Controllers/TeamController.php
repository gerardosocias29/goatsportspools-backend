<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Team, NcaaTeam};
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index(Request $request) {
        $query = Team::query();

        // Filter by league if provided
        if ($request->has('league') && $request->league) {
            $query->where('league', $request->league);
        }

        $teams = $query->orderBy('name', 'ASC')->get();
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

    public function store(Request $request) {
        $user = Auth::user();

        if ($user->role_id > 2) {
            return response()->json(['status' => false, 'message' => 'You do not have permission to create teams.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'league' => 'required|string|in:NFL,NBA,PBA',
            'nickname' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:10',
            'conference' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:500',
        ]);

        $team = Team::create([
            'name' => $request->name,
            'league' => $request->league,
            'nickname' => $request->nickname ?? '',
            'code' => $request->code ?? '',
            'conference' => $request->conference ?? '',
            'image_url' => $request->image_url,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Team created successfully.',
            'data' => $team
        ]);
    }

    public function update(Request $request, $id) {
        $user = Auth::user();

        if ($user->role_id > 2) {
            return response()->json(['status' => false, 'message' => 'You do not have permission to update teams.'], 403);
        }

        $team = Team::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'league' => 'required|string|in:NFL,NBA,PBA',
            'nickname' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:10',
            'conference' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:500',
        ]);

        $team->update([
            'name' => $request->name,
            'league' => $request->league,
            'nickname' => $request->nickname ?? $team->nickname,
            'code' => $request->code ?? $team->code,
            'conference' => $request->conference ?? $team->conference,
            'image_url' => $request->image_url ?? $team->image_url,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Team updated successfully.',
            'data' => $team
        ]);
    }

    public function destroy($id) {
        $user = Auth::user();

        if ($user->role_id != 1) {
            return response()->json(['status' => false, 'message' => 'Only superadmins can delete teams.'], 403);
        }

        $team = Team::findOrFail($id);
        $team->delete();

        return response()->json([
            'status' => true,
            'message' => 'Team deleted successfully.'
        ]);
    }

    public function ncaaIndex() {
        $teams = NcaaTeam::get();
        return response()->json($teams);
    }

}
