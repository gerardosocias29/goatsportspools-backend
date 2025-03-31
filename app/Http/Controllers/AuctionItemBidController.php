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

        $userId = env('DEFAULT_ANONYMOUS_USER_ID', 1);
        if($request->has('user_id')){
            $userId = $request->user_id;
        }

        $auctionItem = AuctionItem::where('id', $item_id)->where('auction_id', $auction_id)->firstOrFail();

        // check auctionItemBid for that amount
        $highestBid = AuctionItemBid::where('auction_item_id', $auctionItem->id)->max('bid_amount') ?? 0;
        $nextMinimumBid = $auctionItem->starting_bid;
        if(!empty($highestBid)){
            $nextMinimumBid = $auctionItem->minimum_bid + $highestBid;
        }
        
        if ($request->bid_amount < $nextMinimumBid) {
            return response()->json([
                'status' => false,
                'message' => 'Your bid must be higher than the current highest bid.',
            ]);
        }

        $totals = AuctionController::getRemainingBalance($auction_id, $userId);
       
        if(!empty($totals['total_budget'])){
            $totalRemaining = $totals['remaining_balance'];
            if($request->bid_amount > $totalRemaining) {
                return response()->json([
                    'status' => false,
                    'message' => "Your bid exceeds your remaining budget of $" . number_format($totalRemaining, 2) . ".",
                ]);
            }
        }

        $checkBid = AuctionItemBid::where('auction_item_id', $auctionItem->id)
            ->where('bid_amount', $request->bid_amount)
            ->first();
            
        if(empty($checkBid)){
            // possible apply bid disable button for all

            $bid = AuctionItemBid::create([
                'auction_item_id' => $auctionItem->id,
                'user_id' => $userId,
                'bid_amount' => $request->bid_amount,
            ]);

            if($request->bid_amount > 100 && $request->bid_amount < 601){
                $auctionItem->minimum_bid = 5;
            } else if($request->bid_amount > 600 && $request->bid_amount < 3001) {
                $auctionItem->minimum_bid = 10;
            } else if($request->bid_amount > 3000 && $request->bid_amount < 9001) {
                $auctionItem->minimum_bid = 30;
            } else if($request->bid_amount > 9001 && $request->bid_amount < 20001) {
                $auctionItem->minimum_bid = 50;
            } else if($request->bid_amount > 20000) {
                $auctionItem->minimum_bid = 100;
            }

            $auctionItem->save();
    
            $bid->load('user');

            PushNotification::notifyBid($bid);

            return response()->json(['status' => true,'message' => 'Bid placed successfully', 'bid' => $bid]);
        }

        return response()->json(['status' => false, 'message' => 'Someone has already bid that amount.']);
    }

    public function removeBid(Request $request) {
        $bid = AuctionItemBid::find($request->bid_id);

        if (!$bid) {
            return response()->json(['status' => false, 'message' => 'Bid not found.']);
        }
    
        // Store auction_item_id before deleting
        $auctionItemId = $bid->auction_item_id;
    
        // Delete the bid first
        $bid->delete();
    
        // Get the new latest bid (after deletion)
        $latestBid = AuctionItemBid::where('auction_item_id', $auctionItemId)
            ->latest('created_at')
            ->first();

        $auctionItem = AuctionItem::find($auctionItemId);

        if($latestBid->bid_amount > 100 && $latestBid->bid_amount < 601){
            $auctionItem->minimum_bid = 5;
        } else if($latestBid->bid_amount > 600 && $latestBid->bid_amount < 3001) {
            $auctionItem->minimum_bid = 10;
        } else if($latestBid->bid_amount > 3000 && $latestBid->bid_amount < 9001) {
            $auctionItem->minimum_bid = 30;
        } else if($latestBid->bid_amount > 9000 && $latestBid->bid_amount < 20001) {
            $auctionItem->minimum_bid = 50;
        } else if($latestBid->bid_amount > 20000) {
            $auctionItem->minimum_bid = 100;
        }
        $auctionItem->save();
    
        if ($latestBid) {
            $latestBid->load('user');
            PushNotification::notifyBid($latestBid);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Bid removed successfully',
            'latest_bid' => $latestBid
        ]);
    }
}
