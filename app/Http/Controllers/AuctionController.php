<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Auction, AuctionUser, AuctionBidDetail, AuctionItem, NcaaTeam};
use App\Events\AuctionStarted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\CustomLibraries\PushNotification;

class AuctionController extends Controller
{
    public function all(){
        $auctions = Auction::with(['items.bids.user'])->get();
        return response()->json($auctions);
    }

    public function getAuctions(Request $request) {
        $user = Auth::user();
        $filter = json_decode($request->filter);

        $auctionsQuery = Auction::with(['items.bids.user', 'items.owner', 'items.ncaa_team',
            'items.bids' => function ($query) {
                $query->orderBy('bid_amount', 'desc'); // Change to 'desc' for highest first
            }
        ]);
        $auctionsQuery = $this->applyFilters($auctionsQuery->orderBy('created_at', 'DESC'), $filter);

        $auctions = $auctionsQuery->paginate($filter->rows, ['*'], 'page', $filter->page + 1);
        return response()->json($auctions);
    }

    public function getAuctionsById(Request $request, $auctionId) {
        $user = Auth::user();
        $auction = Auction::with(['joinedUsers.user', 'items.ncaa_team', 'items.bids.user', 
            'items' => function ($query) {
                $query->whereNull('sold_to');
            }, 
            'joinedUsers' => function ($query) {
                $query->where('status', 'joined');
            }, 
            'items.bids' => function ($query) {
                
                $query->orderBy('bid_amount', 'desc'); // Change to 'desc' for highest first
            }
        ])->where('id', $auctionId)->first();

        $auction->teams = NcaaTeam::all();

        return response()->json($auction);
    }

    public function create(Request $request) {
        $user = Auth::user();
        if($user->role_id == 3) {
            return response()->json(["status" => false, "message" => "You don't have enough permissions to create game."]);
        }

        $request->validate([
            'name' => 'required|string|unique:auctions,name',
            'stream_url' => 'nullable|url',
            'event_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Create Auction
            $auction = Auction::create([
                'name' => $request->name,
                'stream_url' => $request->stream_url,
                'event_date' => \Carbon\Carbon::parse($request->event_date)->toDateTimeString(),
                'status' => 'pending',
            ]);

            // Add Auction Items
            $teams = NcaaTeam::all();
            foreach ($teams as $team) {
                AuctionItem::create([
                    'auction_id' => $auction->id,
                    'ncaa_team_id' => $team->id,
                    'name' => $team['nickname'] . " - ".$team['school'],
                    'description' => $team['school'] ?? null,
                    'starting_bid' => 1,
                    'minimum_bid' => 1,
                    'target_bid' => null,
                ]);
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Auction event created successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create auction event', 'message' => $e->getMessage()], 500);
        }
    }

    public function setStreamUrl(Request $request, $auction_id) {
        $request->validate([
            'stream_url' => 'required|url',
        ]);

        $user = Auth::user();
        if($user->role_id == 3) {
            return response()->json(["status" => false, "message" => "You don't have enough permissions to proceed."]);
        }

        $auction = Auction::findOrFail($auction_id);
        $auction->update([
            'stream_url' => $request->stream_url,
            "status" => "live"
        ]);

        PushNotification::notifyActiveAuction($auction);
        PushNotification::notifyActiveAuction(["status" => true, "data" => $auction], $user->id);

        // broadcast(new AuctionStarted($auction));

        return response()->json(['status' => true, 'message' => 'Stream URL updated successfully', 'auction' => $auction], 200);
    }
    
    public function setActiveItem(Request $request, $auction_id, $item_id) {
        $user = Auth::user();
        if($user->role_id == 3) {
            return response()->json(["status" => false, "message" => "You don't have enough permissions to proceed."]);
        }

        $auctionItem = AuctionItem::with(['bids.user', 'bids' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->where('id', $item_id)
            ->where('auction_id', $auction_id)
            ->first();

        PushNotification::notifyActiveItem(["status" => true, "data" => $auctionItem->id, "message" => "Get active item"]);
        
        return response()->json(['status' => true, 'message' => 'All users notified.'], 200);
    }

    public function getActiveItem($auction_id, $item_id) {
        $user = Auth::user();
        $auctionItem = AuctionItem::with(['bids.user', 'bids' => function ($query) {
            $query->orderBy('bid_amount', 'desc');
        }])
        ->where('id', $item_id)
        ->where('auction_id', $auction_id)
        ->first();

        return response()->json($auctionItem);
    }

    public function end(Request $request, $auction_id, $item_id) {
        $user = Auth::user();
        $auctionItem = AuctionItem::where('auction_id', $auction_id)->where('id', $item_id)->first();
        
        if($request->sold_to != 0 && $request->sold_amount != 0){
            $auctionItem->sold_to = $request->sold_to;
            $auctionItem->sold_amount = $request->sold_amount;
            $auctionItem->save();

            PushNotification::notifyActiveItem(["status" => true, "data" => 0, "message" => "End active item"]);
        }
        
        return response()->json(['status' => true, 'message' => 'Auction item ended.']);
    }

    public function getUpcomingAuctions(Request $request)
    {
        $auctions = Auction::where('status', 'pending')->with(['items.bids'])->get();
        return response()->json($auctions);
    }

    public function getLiveAuction(Request $request)
    {
        $liveAuction = Auction::where('status', 'live')->with(["items.bids"])->first();
        return response()->json($liveAuction);
    }

    public function getUserAuctionedItems()
    {
        $user = Auth::user();
        $items = AuctionItem::whereHas('bids', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return response()->json($items);
    }

    private function applyFilters($query, $filter) {
        if (!empty($filter->filters->global->value)) {
            $query->where(function (Builder $query) use ($filter) {
                $value = '%' . $filter->filters->global->value . '%';
                $auction = new Auction();
                foreach ($auction->getFillable() as $column) {
                    $query->orWhere($column, 'LIKE', $value);
                }
            });
        }
        return $query;
    }
    
    public function getAuctionDetails(Request $request, $auction_id, $ncaa_team_id) {
        $auctionBidDetail = AuctionBidDetail::where('auction_id', $auction_id)
            ->where('ncaa_team_id', $ncaa_team_id)
            ->orderBy('created_at', 'DESC')
            ->first();
        
        if(!$auctionBidDetail){
            $auctionBidDetail = new AuctionBidDetail();
            $auctionBidDetail->auction_id = $auction_id;
            $auctionBidDetail->ncaa_team_id = $ncaa_team_id;
            $auctionBidDetail->starting_bid = 1;
            $auctionBidDetail->minimum_bid = 1;
            $auctionBidDetail->save();
        }

        return response()->json($auctionBidDetail);
    }

    public function auctionJoin($auctionId) {
        $user = Auth::user();

        $auctionUser = AuctionUser::where('auction_id', $auctionId)
        ->where('user_id', $user->id)
        ->first();

        if ($auctionUser) {
            // Update existing record
            $auctionUser->status = "joined";
            $auctionUser->save();
        } else {
            // Create a new record
            $auctionUser = new AuctionUser();
            $auctionUser->auction_id = $auctionId;
            $auctionUser->user_id = $user->id;
            $auctionUser->status = "joined";
            $auctionUser->save();
        }

        PushNotification::notifyAuctionJoined(["status" => true, "message" => "Get joined members on auction."]);
        return response()->json(["status" => true, "message" => "Joined!"]);
    }

    public function auctionAway($auctionId, $userId) {
        $auctionUser = AuctionUser::where('user_id', $userId)->where('auction_id', $auctionId)->first();
        if(!empty($auctionUser)) {
            $auctionUser->status = "away";
            $auctionUser->update();

            PushNotification::notifyAuctionJoined(["status" => true, "message" => "Get joined members on auction."]);
        }
        return response()->json(["status" => true, "message" => "Away!"]);
    }

    public function auctionMembers($auctionId) {
        $auctionUsers = AuctionUser::with(['user'])->where('auction_id', $auctionId)
            ->where("status", "joined")
            ->get();

        return response()->json($auctionUsers);
    }

    public function endAuction($auction_id) {
        $auction = Auction::where('id', $auction_id)->first();
        $auction->status = "pending";
        $auction->save();

        return response()->json(["status" => true, "message" => "Auction Ended"]);
    }
}
