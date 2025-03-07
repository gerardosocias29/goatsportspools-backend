<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Auction, NcaaTeam};
use App\Models\AuctionItem;
use Illuminate\Validation\ValidationException;

class AuctionItemController extends Controller
{
    public function storeBracket(Request $request, $auctionId)
    {
        $validatedData = $request->validate([
            'teams' => 'required|array|min:2',
            'teams.*.team' => 'required|integer|exists:ncaa_teams,id',
            'teams.*.seed' => 'required|string|max:3',
            'teams.*.region' => 'required|string|in:East,West,South,Midwest',
        ]);

        $auction = Auction::findOrFail($auctionId);

        foreach ($validatedData['teams'] as $teamData) {
            $team = NcaaTeam::find($teamData['team']);

            AuctionItem::create([
                'auction_id' => $auction->id,
                'ncaa_team_id' => $team->id,
                'name' => $team->nickname,
                'description' => $team->school,
                'seed' => $teamData['seed'],
                'region' => $teamData['region'],
                'starting_bid' => 1,
                'minimum_bid' => 1,
            ]);
        }

        return response()->json(["status" => true, 'message' => 'Bracket submitted successfully.']);
    }
}
