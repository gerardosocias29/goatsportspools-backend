<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Auction, AuctionItem, AuctionItemBid, AuctionUser};
use Illuminate\Support\Facades\Auth;
use App\CustomLibraries\PushNotification;

class AuctionItemBidController extends Controller
{
    public function placeBid(Request $request, $auction_id, $item_id)
    {
        $request->validate(['bid_amount' => 'required|numeric|min:0']);

        $user = Auth::user();

        // Superadmin can bid on behalf of others via user_id param; regular users always use their own ID
        $userId = $user->id;
        if ($request->has('user_id') && $user->role_id === 1) {
            $userId = $request->user_id;
        }

        // Escrow gate on bid (superadmin bypasses) — user must have an auction_users record
        $biddingUserId = $userId;
        if ($user->role_id !== 1) {
            $auctionUser = AuctionUser::where('auction_id', $auction_id)
                ->where('user_id', $biddingUserId)
                ->first();
            if (!$auctionUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not assigned to this auction.',
                ], 403);
            }
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

            $auctionItem->minimum_bid = self::getMinimumBidIncrement($request->bid_amount);

            $auctionItem->save();

            $bid->load('user');

            // Include updated minimum_bid in the Pusher event so frontend can recalculate next bid
            $bidData = $bid->toArray();
            $bidData['minimum_bid'] = $auctionItem->minimum_bid;

            PushNotification::notifyBid($bidData);

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

        if ($latestBid) {
            $auctionItem->minimum_bid = self::getMinimumBidIncrement($latestBid->bid_amount);
        } else {
            $auctionItem->minimum_bid = 1;
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

    private static function getMinimumBidIncrement($bidAmount)
    {
        if ($bidAmount > 20000) return 100;
        if ($bidAmount > 9000) return 50;
        if ($bidAmount > 3000) return 30;
        if ($bidAmount > 600) return 10;
        if ($bidAmount > 100) return 5;
        return 1;
    }
}
