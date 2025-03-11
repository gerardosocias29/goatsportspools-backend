<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Auction, NcaaTeam};
use App\Models\AuctionItem;
use Illuminate\Validation\ValidationException;

class AuctionItemController extends Controller
{
    public function storeBracket(Request $request, $auctionId) {
        $validatedData = $request->validate([
            'teams' => 'required|array|min:2',
            'teams.*.id' => 'nullable|integer|exists:auction_items,id',
            'teams.*.team' => 'required|integer|exists:ncaa_teams,id',
            'teams.*.seed' => 'required|string|max:3',
            'teams.*.region' => 'required|string|in:East,West,South,Midwest',
        ]);

        $auction = Auction::findOrFail($auctionId);
        $existingItems = AuctionItem::where('auction_id', $auction->id)->get()->keyBy('id');

        $processedIds = [];

        foreach ($validatedData['teams'] as $teamData) {
            $team = NcaaTeam::findOrFail($teamData['team']);

            if (!empty($teamData['id']) && isset($existingItems[$teamData['id']])) {
                // Update existing auction item
                $auctionItem = $existingItems[$teamData['id']];
                $auctionItem->update([
                    'ncaa_team_id' => $team->id,
                    'name' => $team->nickname,
                    'description' => $team->school,
                    'seed' => $teamData['seed'],
                    'region' => $teamData['region'],
                ]);
                $processedIds[] = $auctionItem->id;
            } else {
                // Create new auction item
                $newItem = AuctionItem::create([
                    'auction_id' => $auction->id,
                    'ncaa_team_id' => $team->id,
                    'name' => $team->nickname,
                    'description' => $team->school,
                    'seed' => $teamData['seed'],
                    'region' => $teamData['region'],
                    'starting_bid' => 1,
                    'minimum_bid' => 1,
                ]);
                $processedIds[] = $newItem->id;
            }
        }

        // Delete removed auction items
        AuctionItem::where('auction_id', $auction->id)->whereNotIn('id', $processedIds)->delete();

        $auction->update(['is_finalized' => true]);

        return response()->json(["status" => true, 'message' => 'Bracket submitted successfully.']);
    }

}
