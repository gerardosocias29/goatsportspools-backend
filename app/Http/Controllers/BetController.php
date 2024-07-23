<?php

namespace App\Http\Controllers;

use App\Models\{Bet, Game, LeagueParticipant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class BetController extends Controller
{
    public function index(Request $request) {
        $userId = Auth::user()->id;

        $filter = json_decode($request->filter);
        $betsQuery = Bet::with(['wagerType', 'game.home_team', 'game.visitor_team', 'team', 'odd.favored_team', 'odd.underdog_team'])->where('user_id', $userId);

        $betsQuery = $this->applyFilters($betsQuery->orderBy('id', 'DESC'), $filter);
        $bets = $betsQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));

        return response()->json($bets);
    }

    private function applyFilters($query, $filter) {
        if (!empty($filter->filters->global->value)) {
            $query->where(function (Builder $query) use ($filter) {
                $value = '%' . $filter->filters->global->value . '%';
                $bet = new Bet();
                foreach ($bet->getFillable() as $column) {
                    $query->orWhere($column, 'LIKE', $value);
                }
            });
        }
        return $query;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if(count($request->bets) < 1){
            return response()->json(["status" => false, "message" => "Empty Bets."]);
        }

        $gameIds = array_column($request->bets, 'game_id');
        $games = Game::whereIn('id', $gameIds)->get()->keyBy('id');
        
        $firstBet = $request->bets[0];
        $leagueParticipant = LeagueParticipant::where('league_id', $firstBet['league_id'])->where('user_id', $user->id)->first();
        if (!$leagueParticipant) { return response()->json(["status" => false, "message" => "Unable to place bets. You are not a member of this league. Consider joining a league."]); }

        // Calculate total wager amount
        $totalWagerAmount = array_sum(array_column($request->bets, 'wager_amount'));

        // Check if user's balance is sufficient
        $currentBalance = $leagueParticipant->balance;
        if ($totalWagerAmount > $currentBalance) {
            return response()->json(["status" => false, "message" => "Insufficient balance to place bets."]);
        }

        foreach ($request->bets as $betData) {
            $game = $games->get($betData['game_id']);
            if (!$game) { return response()->json(["status" => false, "message" => "Game not found."]); }
            $gameDatetime = $game->game_datetime; // Assuming `datetime` is the field name for the game time
            $currentUtcTime = \Carbon\Carbon::now()->utc();
            $gameTime = \Carbon\Carbon::parse($gameDatetime)->utc();
            
            if ($currentUtcTime->gte($gameTime->copy()->subMinutes(5))) {
                return response()->json(["status" => false, "message" => "Betting is closed for this game."]);
            }

            $game = Game::where('id', $betData['game_id'])->first();

            $bet = new Bet();
            $bet->game_id = $betData['game_id'];
            $bet->user_id = $user->id;
            $bet->pool_id = $betData['pool_id'];
            $bet->league_id = $betData['league_id'];
            $bet->wager_type_id = $betData['wager_type_id'];
            $bet->odd_id = $betData['odd_id'];
            $bet->team_id = $betData['team_id'] ?? 0;
            $bet->picked_odd = $betData['pick_odd'];
            $bet->wager_amount = $betData['wager_amount'];
            $bet->wager_result = 'pending';

            $win_amount = $betData['wager_amount'];
            if($betData['wager_type_id'] == 3){ // moneyline
                $win_amount = $this->calculateMoneylineWinnings($win_amount, $betData['pick_odd']);
            }

            $bet->wager_win_amount = $win_amount;
            $bet->bet_type = $betData['bet_type'];
            $bet->save();

            $bet->ticket_number = \Carbon\Carbon::now()->format('ym').str_pad($bet->id, 6, "0", STR_PAD_LEFT);
            $bet->update();

            LeagueController::updateLeagueUserBalanceHistory($bet->league_id, $user->id, -$betData['wager_amount'], 'bet');
        }

        return response()->json(["status" => true, "message" => "Bets placed successfully."]);
    }

    public function calculateMoneylineWinnings($betAmount, $moneylineOdds) {
        $winnings = 0;
        if ($moneylineOdds > 0) {
            $winnings = $betAmount * ($moneylineOdds / 100);
        } else {
            $winnings = $betAmount * (100 / abs($moneylineOdds));
        }
    
        return $winnings;
    }
}
