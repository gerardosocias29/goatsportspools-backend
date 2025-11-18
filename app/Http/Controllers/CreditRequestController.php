<?php

namespace App\Http\Controllers;

use App\Models\CreditRequest;
use App\Models\AdminCreditRequest;
use App\Models\SquaresPool;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditRequestController extends Controller
{
    /**
     * Request credits from pool commissioner (Player → Commissioner)
     */
    public function requestCreditsFromCommissioner(Request $request, $poolId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $pool = SquaresPool::findOrFail($poolId);
        $userId = auth()->id();

        // Get commissioner ID (admin_id or created_by)
        $commissionerId = $pool->admin_id ?? $pool->created_by;

        if (!$commissionerId) {
            return response()->json(['error' => 'Pool has no commissioner assigned'], 400);
        }

        // Check if user is already in the pool (has joined)
        $isInPool = $pool->players()->where('player_id', $userId)->exists()
                    || $pool->squares()->where('player_id', $userId)->exists();

        if (!$isInPool) {
            return response()->json(['error' => 'You must join the pool before requesting credits'], 403);
        }

        // Check for existing pending request
        $existingRequest = CreditRequest::where('pool_id', $poolId)
            ->where('requester_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json(['error' => 'You already have a pending credit request for this pool'], 400);
        }

        $creditRequest = CreditRequest::create([
            'pool_id' => $poolId,
            'requester_id' => $userId,
            'commissioner_id' => $commissionerId,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Credit request submitted successfully',
            'credit_request' => $creditRequest->load(['requester', 'pool']),
        ], 201);
    }

    /**
     * Get credit requests for pools where current user is commissioner
     */
    public function getCommissionerRequests(Request $request)
    {
        $userId = auth()->id();

        $requests = CreditRequest::with(['requester', 'pool'])
            ->forCommissioner($userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    /**
     * Get credit requests for a specific pool (Commissioner only)
     */
    public function getPoolRequests($poolId)
    {
        $userId = auth()->id();
        $pool = SquaresPool::findOrFail($poolId);

        // Check if user is commissioner of this pool
        $commissionerId = $pool->admin_id ?? $pool->created_by;

        if ($commissionerId !== $userId && auth()->user()->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $requests = CreditRequest::with(['requester'])
            ->forPool($poolId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    /**
     * Approve or deny a credit request (Commissioner or Superadmin)
     */
    public function updateCreditRequest(Request $request, $requestId)
    {
        $request->validate([
            'status' => 'required|in:approved,denied',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $creditRequest = CreditRequest::with(['pool', 'requester'])->findOrFail($requestId);
        $userId = auth()->id();
        $userRoleId = auth()->user()->role_id;

        // Check authorization: must be commissioner or superadmin
        $commissionerId = $creditRequest->pool->admin_id ?? $creditRequest->pool->created_by;

        if ($commissionerId !== $userId && $userRoleId !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $creditRequest->update([
                'status' => $request->status,
                'approved_at' => now(),
                'approved_by' => $userId,
                'admin_note' => $request->admin_note,
            ]);

            // If approved, add credits to user's balance
            if ($request->status === 'approved') {
                $requester = $creditRequest->requester;
                $requester->balance = ($requester->balance ?? 0) + $creditRequest->amount;
                $requester->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Credit request ' . $request->status . ' successfully',
                'credit_request' => $creditRequest->fresh(['requester', 'approver']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update credit request'], 500);
        }
    }

    /**
     * Request credits from Superadmin (Square Admin → Superadmin)
     */
    public function requestCreditsFromSuperadmin(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:500',
        ]);

        $user = auth()->user();

        // Only Square Admins (role_id = 2) can request from Superadmin
        if ($user->role_id !== 2) {
            return response()->json(['error' => 'Only Square Admins can request credits from Superadmin'], 403);
        }

        // Check for existing pending request
        $existingRequest = AdminCreditRequest::where('requester_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json(['error' => 'You already have a pending credit request'], 400);
        }

        $creditRequest = AdminCreditRequest::create([
            'requester_id' => $user->id,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Credit request submitted successfully',
            'credit_request' => $creditRequest->load('requester'),
        ], 201);
    }

    /**
     * Get all admin credit requests (Superadmin only)
     */
    public function getSuperadminRequests(Request $request)
    {
        $user = auth()->user();

        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $requests = AdminCreditRequest::with(['requester'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    /**
     * Approve or deny an admin credit request (Superadmin only)
     */
    public function updateAdminCreditRequest(Request $request, $requestId)
    {
        $request->validate([
            'status' => 'required|in:approved,denied',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();

        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $creditRequest = AdminCreditRequest::with('requester')->findOrFail($requestId);

        DB::beginTransaction();
        try {
            $creditRequest->update([
                'status' => $request->status,
                'approved_at' => now(),
                'approved_by' => $user->id,
                'admin_note' => $request->admin_note,
            ]);

            // If approved, add credits to Square Admin's balance
            if ($request->status === 'approved') {
                $requester = $creditRequest->requester;
                $requester->balance = ($requester->balance ?? 0) + $creditRequest->amount;
                $requester->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Credit request ' . $request->status . ' successfully',
                'credit_request' => $creditRequest->fresh(['requester', 'approver']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update credit request'], 500);
        }
    }

    /**
     * Get current user's credit requests
     */
    public function getMyRequests(Request $request)
    {
        $userId = auth()->id();
        $userRoleId = auth()->user()->role_id;

        // For regular users and Square Admins, get their pool credit requests
        $poolRequests = CreditRequest::with(['pool', 'commissioner', 'approver'])
            ->where('requester_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $response = [
            'pool_requests' => $poolRequests,
        ];

        // For Square Admins, also get their admin credit requests
        if ($userRoleId === 2) {
            $adminRequests = AdminCreditRequest::with(['approver'])
                ->where('requester_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            $response['admin_requests'] = $adminRequests;
        }

        return response()->json($response);
    }
}
