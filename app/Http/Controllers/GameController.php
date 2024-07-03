<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function games(Request $request) {
        $userId = Auth::user()->id;

        $filter = json_decode($request->filter);
        $gamesQuery = Game::with(['odd.favored_team', 'odd.underdog_team']);

        $gamesQuery = $this->applyFilters($gamesQuery, $filter);
        $games = $gamesQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));

        return response()->json($games);
    }

    private function applyFilters($query, $filter) {
        if (!empty($filter->filters->global->value)) {
            $query->where(function (Builder $query) use ($filter) {
                $value = '%' . $filter->filters->global->value . '%';
                $game = new Game();
                foreach ($game->getFillable() as $column) {
                    $query->orWhere($column, 'LIKE', $value);
                }
            });
        }
        return $query;
    }
}
