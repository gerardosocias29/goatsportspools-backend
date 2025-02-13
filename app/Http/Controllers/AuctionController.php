<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Auction, AuctionItem};
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

        $auctionsQuery = Auction::with(['items']);
        $auctionsQuery = $this->applyFilters($auctionsQuery->orderBy('created_at', 'DESC'), $filter);

        $auctions = $auctionsQuery->paginate($filter->rows, ['*'], 'page', $filter->page + 1);
        return response()->json($auctions);
    }

    public function getAuctionsById(Request $request, $auctionId) {
        $user = Auth::user();
        $auction = Auction::with(['items.bids.user', 'items.bids' => function ($query) {
            $query->orderBy('created_at', 'desc'); // Change to 'desc' for newest first
        }])->where('id', $auctionId)->first();
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
            'items' => 'required|array',
            'items.*.name' => 'required|string|unique:auction_items,name',
            'items.*.description' => 'nullable|string',
            'items.*.starting_bid' => 'required|numeric|min:0',
            'items.*.minimum_bid' => 'required|numeric|min:0',
            'items.*.target_bid' => 'nullable|numeric|min:0',
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
            foreach ($request->items as $item) {
                AuctionItem::create([
                    'auction_id' => $auction->id,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'starting_bid' => $item['starting_bid'],
                    'minimum_bid' => $item['minimum_bid'],
                    'target_bid' => $item['target_bid'] ?? null,
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

        PushNotification::notifyActiveAuction(["status" => true, "message" => "Get fresh auctions data"]);
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

        PushNotification::notifyActiveItem(["status" => true, "data" => $auctionItem, "message" => "Get active item"]);
        
        return response()->json(['status' => true, 'message' => 'All users notified.'], 200);
    }

    public function getUpcomingAuctions()
    {
        $auctions = Auction::where('status', 'upcoming')->with('items')->get();
        return response()->json($auctions);
    }

    public function getLiveAuction()
    {
        $liveAuction = Auction::where('status', 'live')->with('items.bids')->first();
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
    
}
