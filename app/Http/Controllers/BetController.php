<?php

namespace App\Http\Controllers;

use App\Models\{Bet, BetGroup, Game, LeagueParticipant, WagerType};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class BetController extends Controller
{
    public function totalAtRisk(Request $request) {
        $user = Auth::user();

        $betsRisk = 0;
        $betGroupRisks = 0;

        $betsRisks = Bet::where('user_id', $user->id)->where('wager_result', 'pending')->sum('wager_amount');
        $betGroupRisks = BetGroup::where('user_id', $user->id)->where('wager_result', 'pending')->sum('wager_amount');

        $totalBalance = LeagueParticipant::where('user_id', $user->id)
            ->selectRaw("SUM(balance) as total_balance")
            ->groupBy('user_id')
            ->pluck('total_balance')
            ->first();

        return response()->json(["status" => true, "at_risk" => $betsRisk + $betGroupRisks, 'total_balance' => $totalBalance]);
    }

    public function index(Request $request) {
        $userId = Auth::user()->id;
    
        $filter = json_decode($request->filter);
        $betsQuery = Bet::with([
            'wagerType', 'game.home_team', 'game.visitor_team', 'team', 'odd.favored_team', 'odd.underdog_team', 
            'betGroup.bets', 'betGroup.wagerType',
            'betGroup.bets.wagerType', 'betGroup.bets.game.home_team', 'betGroup.bets.game.visitor_team', 'betGroup.bets.team', 'betGroup.bets.odd.favored_team', 'betGroup.bets.odd.underdog_team'
        ])->where('user_id', $userId);
    
        // Apply filters and order by
        $betsQuery = $this->applyFilters($betsQuery->orderBy('id', 'DESC'), $filter);
    
        // Get all bets
        $bets = $betsQuery->get();
    
        $mergedBets = collect();
    
        $betsRisk = $betsQuery->where('wager_result', 'pending')->sum('wager_amount');
        $groupBetRisk = BetGroup::where('wager_result', 'pending')->where('user_id', $userId)->sum('wager_amount');
    
        $totalAtRisk = $betsRisk + $groupBetRisk;
    
        foreach ($bets as $bet) {
            if ($bet->bet_group_id) {
                $existingGroup = $mergedBets->firstWhere('bet_group_id', $bet->bet_group_id);
                if ($existingGroup) {
                    // Add bet to existing bet group
                    $existingGroup->merged_bets->push($bet);
                } else {
                    $betCopy = clone $bet;
                    $betCopy->merged_bets = collect([$bet]);
                    $mergedBets->push($betCopy);
                }
            } else {
                // No bet group, add as individual bet
                $mergedBets->push($bet);
            }
        }
    
        // Prepare the response with all data and count
        $response = [
            'data' => $mergedBets,
            'total' => $mergedBets->count(),
            'total_at_risk' => $totalAtRisk,
        ];
    
        return response()->json($response);
    }    
    
    public function getOne(Request $request, $user_id) {
        $betsQuery = Bet::with([
            'wagerType', 
            'game',
            'game.home_team', 
            'game.visitor_team', 
            'team', 
            'odd.favored_team', 
            'odd.underdog_team', 
            'betGroup' => function ($query) {
                $query->where('wager_result', '!=', 'pending');
            }, 
            'betGroup.bets' => function ($query) {
                $query->where('wager_result', '!=', 'pending');
            }, 
            'betGroup.wagerType',
            'betGroup.bets.wagerType', 
            'betGroup.bets.game.home_team', 
            'betGroup.bets.game.visitor_team', 
            'betGroup.bets.team', 
            'betGroup.bets.odd.favored_team', 
            'betGroup.bets.odd.underdog_team'
        ])
        ->where('user_id', $user_id)
        ->where('wager_result', '!=', 'pending');
        
        $bets = $betsQuery->get();

        $mergedBets = collect();
        
        foreach ($bets as $key => $bet) {
            if ($bet->bet_group_id) {
                $existingGroup = $mergedBets->firstWhere('bet_group_id', $bet->bet_group_id);
                if ($existingGroup) {
                    // Add bet to existing bet group
                    $existingGroup->merged_bets->push($bet);
                } else {
                    $betCopy = clone $bet;
                    $betCopy->merged_bets = collect([$bet]);
                    $mergedBets->push($betCopy);
                }
            } else {
                // No bet group, add as individual bet
                $mergedBets->push($bet);
            }
        }

        return response()->json($mergedBets);
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

        $betGroupId = 0;
        if($request->wager_type == "parlay" || strpos($request->wager_type, 'teaser') !== false) {
            $wagerTypeNameExt = "TP";
            $adjustment = 0;
            if(strpos($request->wager_type, 'teaser') !== false){
                $adjustment = $request->wager_type == "teaser_7" ? 7 : ($request->wager_type == "teaser_6_5" ? 6.5 : 6);
                $wagerTypeNameExt = "TT";
            }

            $wagerTypeName = count($request->bets).$wagerTypeNameExt;
            $wagerType = WagerType::where('name', $wagerTypeName)->first();

            if ($request->wager_amount > $currentBalance) {
                return response()->json(["status" => false, "message" => "Insufficient balance to place bets."]);
            }

            $betGroup = new BetGroup();
            $betGroup->user_id = $user->id;
            $betGroup->wager_type_id = $wagerType->id;
            $betGroup->wager_amount = $request->wager_amount;
            $betGroup->wager_win_amount = $request->wager_win_amount;
            $betGroup->league_id = $request->league_id;
            $betGroup->adjustment = $adjustment;
            $betGroup->wager_result = "pending";
            $betGroup->save();

            $betGroupId = $betGroup->id;
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

            if($request->wager_type == "parlay" || strpos($request->wager_type, 'teaser') !== false) {
                $bet->bet_group_id = $betGroupId;
                $bet->update();
            } else {
                LeagueController::updateLeagueUserBalanceHistory($bet->league_id, $user->id, -$betData['wager_amount'], 'bet');
            }
        }

        if($request->wager_type == "parlay") {
            LeagueController::updateLeagueUserBalanceHistory($request->league_id, $user->id, -$request->wager_amount, 'bet');
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
