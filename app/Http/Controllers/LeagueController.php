<?php

namespace App\Http\Controllers;

use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeagueController extends Controller
{
    public function index(Request $request) {
        $filter = json_decode($request->filter);
        $leaguesQuery = League::with(['created_by' => function($query){
            $query->select('id', 'name');
        }]);

        if (Auth::user()->role_id == 2) {
            $leaguesQuery->where('user_id', Auth::user()->id);
        }

        $leaguesQuery = $this->applyFilters($leaguesQuery, $filter);
        $leagues = $leaguesQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));

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
}
