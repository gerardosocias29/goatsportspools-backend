<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Auction, AuctionItem, AuctionItemBid};
use Illuminate\Support\Facades\Auth;
use App\CustomLibraries\PushNotification;

class AuctionItemBidController extends Controller
{
    public function placeBid(Request $request, $auction_id, $item_id)
    {
        $request->validate(['bid_amount' => 'required|numeric|min:0']);

        $user = Auth::user();

        $auctionItem = AuctionItem::where('id', $item_id)->where('auction_id', $auction_id)->firstOrFail();

        $bid = AuctionItemBid::create([
            'auction_item_id' => $auctionItem->id,
            'user_id' => $user->id,
            'bid_amount' => $request->bid_amount,
        ]);

        $bid->load('user');

        PushNotification::notifyBid($bid);

        return response()->json(['message' => 'Bid placed successfully', 'bid' => $bid]);
    }
}
