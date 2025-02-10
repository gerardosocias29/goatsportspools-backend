<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Auction, AuctionItem};
use App\Events\AuctionStarted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class AuctionController extends Controller
{
    public function getAuctions(Request $request) {
        $user = Auth::user();
        $filter = json_decode($request->filter);

        $auctionsQuery = Auction::with(['items']);
        $auctionsQuery = $this->applyFilters($auctionsQuery->orderBy('created_at', 'DESC'), $filter);

        $auctions = $auctionsQuery->paginate($filter->rows, ['*'], 'page', $filter->page + 1);
        return response()->json($auctions);
    }

    public function create(Request $request) {
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
        $auction = Auction::findOrFail($auction_id);
        $auction->update(['stream_url' => $request->stream_url]);

        broadcast(new AuctionStarted($auction));

        return response()->json(['status' => true, 'message' => 'Stream URL updated successfully', 'auction' => $auction], 200);
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
