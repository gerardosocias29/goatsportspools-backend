<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'name' => 'required|string|unique:auctions,name',
            'stream_url' => 'required|url',
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
                'event_date' => $request->event_date,
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
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Auction event created successfully', 'auction' => $auction], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create auction event', 'message' => $e->getMessage()], 500);
        }
    }

}
