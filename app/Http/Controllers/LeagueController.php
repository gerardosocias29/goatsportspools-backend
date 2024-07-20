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

    public function joinedLeagues() {
        $userId = Auth::user()->id;

        $leaguesJoined = League::join('league_participants', 'leagues.id', '=', 'league_participants.league_id')
        ->where('league_participants.user_id', $userId)
        ->select('leagues.*', 'league_participants.balance')
        ->get();


        return response(["status" => true, "leagues_joined" => $leaguesJoined]);
    }

    public function getLeaguesCreatedCount() {
        $user = Auth::user();
        $leagueCreatedCount = League::where('user_id', $user->id)->count();

        return response()->json(["status" => true, "leagues_created" => $leagueCreatedCount]);
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
            if ($league->has_joined) {
                $league->balance = $league->participants()
                    ->where('user_id', $userId)
                    ->select('league_participants.balance') // Assuming 'participants' is the table name
                    ->first()
                    ->balance;
            }
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

        $leagueParticipant = new LeagueParticipant();
        $leagueParticipant->league_id = $league->id;
        $leagueParticipant->user_id = Auth::user()->id;
        $leagueParticipant->balance = 25000;
        $leagueParticipant->save();

        self::updateLeagueUserBalanceHistory($league->id, Auth::user()->id, 25000);

        return response()->json(["status" => true, "message" => "League created successfully."]);
    }

    public function update(Request $request, $league_id)
    {
        $user = Auth::user();
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'nullable|integer',
        ]);

        $league = League::find($league_id);

        if (!empty($validatedData['password']) && $validatedData['password'] != null) {
            $league->password = bcrypt($validatedData['password']); // Assuming password should be hashed
        }

        if (!empty($validatedData['user_id']) && $validatedData['user_id'] != null && $user->role_id == 1) {
            $league->user_id = $validatedData['user_id'];
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
        $leagueParticipant->balance = 25000;
        $leagueParticipant->save();

        self::updateLeagueUserBalanceHistory($leagueId, $userId, 25000);

        return response()->json(['message' => 'Successfully joined the league.', "status" => true]);
    }

    public static function updateLeagueUserBalanceHistory($leagueId, $userId, $amount) {
        $balance = new BalanceHistory();
        $balance->league_id = $leagueId;
        $balance->user_id = $userId;
        $balance->amount = $amount;
        $balance->save();
    }    

}
