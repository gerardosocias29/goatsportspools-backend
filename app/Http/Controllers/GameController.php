<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Game, Bet, BetGroup, WagerType};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    public function games(Request $request) {
        $user = Auth::user();
    
        $filter = json_decode($request->filter);
        $oneHourAgo = \Carbon\Carbon::now()->subHour()->toDateTimeString();
    
        $gamesQuery = Game::with(['home_team', 'visitor_team', 'odd.favored_team', 'odd.underdog_team'])
            ->where(function ($query) {
                $query->where('home_team_score', '=', 0)
                      ->orWhere('visitor_team_score', '=', 0);
            })
            ->orderBy('game_datetime', 'ASC');

        if($user->role_id != 1){
            $gamesQuery->where('game_datetime', '>', $oneHourAgo);
        } else {
            $gamesQuery->where('visitor_team_score', '<', 1);
            $gamesQuery->orWhere('home_team_score', '<', 1);
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
                        } elseif ($adjusted_home_score < $game->visitor_team_score) {
                            $bet->wager_result = 'lose';
                        } else {
                            $bet->wager_result = 'push'; // Adjusted score equals the visitor's score
                        }
                    } elseif ($bet->team_id == $game->visitor_team_id) {
                        $adjusted_visitor_score = $game->visitor_team_score + $spread;
                        if ($adjusted_visitor_score > $game->home_team_score) {
                            $bet->wager_result = 'win';
                        } elseif ($adjusted_visitor_score < $game->home_team_score) {
                            $bet->wager_result = 'lose';
                        } else {
                            $bet->wager_result = 'push'; // Adjusted score equals the home's score
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
                    if ($game->home_team_score == $game->visitor_team_score) {
                        $bet->wager_result = 'push'; // Game ended in a tie
                    } elseif ($bet->team_id == $game->home_team_id && $game->home_team_score > $game->visitor_team_score) {
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
            
            $bet->update();

            if ($bet->wager_result === 'win') {
                if($bet->bet_type == "straight") {
                    $amount = $bet->wager_win_amount + $bet->wager_amount;
                    LeagueController::updateLeagueUserBalanceHistory($leagueId, $bet->user_id, $amount, 'win');
                }
                if($bet->bet_type == "parlay" && $bet->bet_group_id != null) {
                    $this->checkParlayWinning($bet->bet_group_id, $bet->user_id);   
                }
            } else {
                // convert parlay
                if($bet->bet_type == "parlay" && $bet->bet_group_id != null && $bet->wager_result == "push") {
                    $this->reduceParlayTeams($bet->bet_group_id);
                }
            }
        }
    
        return response()->json(['message' => 'Winner announced and bets updated successfully.']);
    }

    public function checkParlayWinning($bet_group_id, $bet_user_id) {
        $betGroup = BetGroup::where('id', $bet_group_id)->first();

        $wagerType = WagerType::where('id', $betGroup->wager_type_id)->first();

        $betsWinCount = Bets::where('bet_group_id', $bet_group_id)->where('bet_type', 'parlay')->where('wager_result', 'win')->count();
        if($betsWinCount == $wagerType->no_of_teams){
            $amount = $betGroup->wager_win_amount + $betGroup->wager_amount;
            LeagueController::updateLeagueUserBalanceHistory($betGroup->league_id, $bet_user_id, $amount, 'win');
        }
    }

    public function reduceParlayTeams($bet_group_id) {
        $betGroup = BetGroup::where('id', $bet_group_id)->first();
        if ($betGroup) {
    
            // Get all bets in the parlay
            $parlayBets = Bet::where('bet_group_id', $bet_group_id)->where('wager_result', '!=', 'push')->get();
            $betCount = count($parlayBets);

            $combinedOdds = 1;
            foreach($parlayBets as $bet){
                if($bet->wager_type_id == 3){
                    $combinedOdds *= $this->americanToDecimal($bet->picked_odd);
                } else {
                    $combinedOdds *= 2;
                }
                $potentialPayout = $betGroup->wager_amount * $combinedOdds;
                $betGroup->wager_win_amount = $potentialPayout;
                $betGroup->save();
            }
        }
    }
    
    private function americanToDecimal($americanOdds) {
        if ($americanOdds > 0) {
            return ($americanOdds / 100) + 1;
        } else {
            return (100 / abs($americanOdds)) + 1;
        }
    }    

}
