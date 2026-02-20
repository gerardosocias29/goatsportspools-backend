<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SquaresPoolWinner;
use App\Models\User;

class PayoutController extends Controller
{
    /**
     * Get current user's payment method
     */
    public function getPaymentMethod()
    {
        $user = Auth::user();

        return response()->json([
            'data' => [
                'payment_type' => $user->payment_type,
                'payment_account_name' => $user->payment_account_name,
                'payment_account_number' => $user->payment_account_number,
                'payment_bank_name' => $user->payment_bank_name,
            ]
        ]);
    }

    /**
     * Update current user's payment method
     */
    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:gcash,maya,bank',
            'payment_account_name' => 'required|string|max:255',
            'payment_account_number' => 'required|string|max:50',
            'payment_bank_name' => 'nullable|required_if:payment_type,bank|string|max:255',
        ]);

        $user = Auth::user();
        $user->update([
            'payment_type' => $request->payment_type,
            'payment_account_name' => $request->payment_account_name,
            'payment_account_number' => $request->payment_account_number,
            'payment_bank_name' => $request->payment_type === 'bank' ? $request->payment_bank_name : null,
        ]);

        return response()->json([
            'message' => 'Payment method updated successfully',
            'data' => [
                'payment_type' => $user->payment_type,
                'payment_account_name' => $user->payment_account_name,
                'payment_account_number' => $user->payment_account_number,
                'payment_bank_name' => $user->payment_bank_name,
            ]
        ]);
    }

    /**
     * Get all winnings for current user across pools
     */
    public function getUserWinnings()
    {
        $user = Auth::user();

        $winnings = SquaresPoolWinner::where('player_id', $user->id)
            ->with(['pool:id,pool_name,pool_number,league', 'square:id,x_coordinate,y_coordinate,x_number,y_number'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $winnings
        ]);
    }

    /**
     * Get all winners across all pools (superadmin only)
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        if ($user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = SquaresPoolWinner::with([
            'pool:id,pool_name,pool_number,league',
            'square:id,x_coordinate,y_coordinate,x_number,y_number',
            'player:id,first_name,last_name,email,username,payment_type,payment_account_name,payment_account_number,payment_bank_name',
            'paidByUser:id,first_name,last_name',
        ]);

        // Filter by status
        if ($request->status === 'pending') {
            $query->where('is_paid', false)->whereNotNull('player_id');
        } elseif ($request->status === 'paid') {
            $query->where('is_paid', true);
        }

        // Filter by pool
        if ($request->pool_id) {
            $query->where('pool_id', $request->pool_id);
        }

        // Search by player name
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('player', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $winners = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $winners
        ]);
    }

    /**
     * Upload proof of transfer and mark winner as paid (superadmin only)
     */
    public function markAsPaid(Request $request, $winnerId)
    {
        $user = Auth::user();
        if ($user->role_id !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $winner = SquaresPoolWinner::findOrFail($winnerId);

        if ($winner->is_paid) {
            return response()->json(['message' => 'This winner has already been paid'], 422);
        }

        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $path = $file->store('payout_proofs', 'public');
            $backendUrl = env('STORAGE_URL');
            $proofPath = $backendUrl . '/' . $path;
        }

        $winner->update([
            'is_paid' => true,
            'paid_at' => now(),
            'proof_image' => $proofPath,
            'paid_by' => $user->id,
        ]);

        $winner->load(['pool:id,pool_name,pool_number', 'player:id,first_name,last_name', 'paidByUser:id,first_name,last_name']);

        return response()->json([
            'message' => 'Winner marked as paid successfully',
            'data' => $winner,
        ]);
    }
}
