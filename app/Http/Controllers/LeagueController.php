<?php

namespace App\Http\Controllers;

use App\Models\{League, LeagueParticipant};
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LeagueController extends Controller
{
    public function totalLeaguesJoined() {
        $userId = Auth::user()->id;

        $totalLeaguesJoined = League::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        return response(["status" => true, "leagues_joined" => $totalLeaguesJoined]);
    }

    public function index(Request $request) {
        $userId = Auth::user()->id;

        $filter = json_decode($request->filter);
        $leaguesQuery = League::with(['created_by' => function($query){
            $query->select('id', 'name');
        }]);

        // if (Auth::user()->role_id == 2) {
        //     $leaguesQuery->where('user_id', Auth::user()->id);
        // }

        $leaguesQuery = $this->applyFilters($leaguesQuery, $filter);
        $leagues = $leaguesQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));

        $leagues->getCollection()->transform(function ($league) use ($userId) {
            $league->has_joined = $league->participants()->where('user_id', $userId)->exists();
            return $league;
        });

        return response()->json($leagues);
    }

    private function applyFilters($query, $filter) {
        if (!empty($filter->filters->global->value)) {
            $query->where(function (Builder $query) use ($filter) {
                $value = '%' . $filter->filters->global->value . '%';
                $league = new League();
                foreach ($league->getFillable() as $column) {
                    $query->orWhere($column, 'LIKE', $value);
                }
            });
        }
        return $query;
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|integer|exists:users,id',
            'password' => 'required',
        ]);

        if (Auth::user()->role_id == 1) {
            $validatedData['user_id'] = $request->input('user_id');
        } elseif (Auth::user()->role_id == 2) {
            $validatedData['user_id'] = Auth::id();
        }
        $validatedData['status'] = 'active';
        $validatedData['league_id'] = generateFormattedString();

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($request->input('password'));
        }

        $league = League::create($validatedData);
        return response()->json(["status" => true, "message" => "League created successfully."]);
    }

    public function update(Request $request, $league_id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $league = League::find($league_id);

        if (!empty($validatedData['password']) && $validatedData['password'] != null) {
            $league->password = bcrypt($validatedData['password']); // Assuming password should be hashed
        }

        $league->update($validatedData);
        return response()->json(["status" => true, "message" => "League updated successfully."]);
    }

    public function destroy(Request $request, $league_id)
    {
        $league = League::find($league_id);
        $league->delete();
        return response()->json(['message' => 'League deleted successfully']);
    }

    public function join(Request $request) {
        $validatedData = $request->validate([
            'password' => 'required',
            'league_id' => 'required|exists:leagues,id',
        ]);

        $password = $validatedData['password'];
        $leagueId = $validatedData['league_id'];

        $league = League::findOrFail($leagueId);

        if (!Hash::check($password, $league->password)) {
            return response()->json(["status" => false, 'message' => 'Incorrect password']);
        }

        // Associate the authenticated user with the league
        $userId = Auth::user()->id;

        $leagueParticipant = new LeagueParticipant();
        $leagueParticipant->league_id = $leagueId;
        $leagueParticipant->user_id = $userId;
        $leagueParticipant->save();

        return response()->json(['message' => 'Successfully joined the league.', "status" => true]);
    }

}
