<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Game, Bet};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function games(Request $request) {
        $userId = Auth::user()->id;
    
        $filter = json_decode($request->filter);
        $oneHourAgo = \Carbon\Carbon::now()->subHour()->toDateTimeString();
    
        $gamesQuery = Game::with(['home_team', 'visitor_team', 'odd.favored_team', 'odd.underdog_team'])
            ->where(function ($query) {
                $query->where('home_team_score', '=', 0)
                      ->orWhere('visitor_team_score', '=', 0);
            })
            ->orderBy('game_datetime', 'ASC');

        if(Auth::user()->role_id != 1){
            $gamesQuery->where('game_datetime', '>', $oneHourAgo);
        }
    
        $gamesQuery = $this->applyFilters($gamesQuery, $filter);
        $games = $gamesQuery->paginate($filter->rows, ['*'], 'page', $filter->page + 1);
    
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

    public function announceWinner(Request $request) {
        // Validate the request data
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'home_team_score' => 'required|integer',
            'visitor_team_score' => 'required|integer',
        ]);
    
        // Retrieve the game and update the scores
        $game = Game::find($request->game_id);
        $game->home_team_score = $request->home_team_score;
        $game->visitor_team_score = $request->visitor_team_score;
        $game->save();
    
        // Retrieve all bets for the game
        $bets = Bet::where('game_id', $game->id)->get();
    
        foreach ($bets as $bet) {
            switch ($bet->wager_type_id) {
                case 1: // Spread
                    $spread = $bet->picked_odd; // Use picked_odd from the bet
                    if ($bet->team_id == $game->home_team_id) {
                        $adjusted_home_score = $game->home_team_score + $spread;
                        if ($adjusted_home_score > $game->visitor_team_score) {
                            $bet->wager_result = 'win';
                        } else {
                            $bet->wager_result = 'lose';
                        }
                    } elseif ($bet->team_id == $game->visitor_team_id) {
                        $adjusted_visitor_score = $game->visitor_team_score + $spread;
                        if ($adjusted_visitor_score > $game->home_team_score) {
                            $bet->wager_result = 'win';
                        } else {
                            $bet->wager_result = 'lose';
                        }
                    }
                    break;
                case 2: // TotalPoints
                    $totalPointsLine = $bet->picked_odd; // Use picked_odd from the bet
                    $totalGameScore = $game->home_team_score + $game->visitor_team_score;
    
                    if ($totalGameScore > $totalPointsLine) {
                        // Bet on over
                        if($bet->team_id == 0){
                            $bet->wager_result = 'win';
                        } else {
                            $bet->wager_result = 'lose';
                        }
                    } elseif ($totalGameScore < $totalPointsLine) {

                        if($bet->team_id < 0){
                            $bet->wager_result = 'win';
                        } else {
                            $bet->wager_result = 'lose';
                        }
                    } else {
                        // Total game score equals the total points line
                        $bet->wager_result = 'push'; // or handle it according to your business rules
                    }
                    break;
    
                case 3: // MoneyLine
                    if ($bet->team_id == $game->home_team_id && $game->home_team_score > $game->visitor_team_score) {
                        $bet->wager_result = 'win';
                    } elseif ($bet->team_id == $game->visitor_team_id && $game->visitor_team_score > $game->home_team_score) {
                        $bet->wager_result = 'win';
                    } else {
                        $bet->wager_result = 'lose';
                    }
                    break;
    
                default:
                    break;
            }
            
            $bet->status = $bet->wager_result;
            $bet->update();

            if ($bet->wager_result === 'win') {
                $amount = $bet->wager_win_amount + $bet->wager_amount;
                LeagueController::updateLeagueUserBalanceHistory($leagueId, $userId, $amount, 'win');
            }
        }
    
        return response()->json(['message' => 'Winner announced and bets updated successfully.']);
    }   

}
