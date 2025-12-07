<?php

namespace App\Http\Controllers;

use App\Models\SquaresAdminApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SquaresAdminApplicationController extends Controller
{
    /**
     * Get current user's application status.
     */
    public function myStatus()
    {
        $application = SquaresAdminApplication::where('user_id', Auth::id())->first();

        if (!$application) {
            return response()->json(['status' => null]);
        }

        return response()->json([
            'status' => $application->status,
            'created_at' => $application->created_at,
            'reviewed_at' => $application->reviewed_at,
            'admin_note' => $application->admin_note,
        ]);
    }

    /**
     * Submit a new application.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'reason' => 'required|string|min:10',
            'experience' => 'nullable|string',
        ]);

        // Check if user already has an application
        $existingApplication = SquaresAdminApplication::where('user_id', Auth::id())->first();
        if ($existingApplication) {
            return response()->json([
                'message' => 'You already have a pending application.',
            ], 400);
        }

        $application = SquaresAdminApplication::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'reason' => $request->reason,
            'experience' => $request->experience,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Application submitted successfully.',
            'data' => $application,
        ], 201);
    }

    /**
     * List all applications (Superadmin only).
     */
    public function index(Request $request)
    {
        // Only Superadmin can view applications
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $status = $request->query('status');

        $query = SquaresAdminApplication::with(['user', 'reviewer']);

        if ($status) {
            $query->where('status', $status);
        }

        $applications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $applications,
        ]);
    }

    /**
     * Update application status (Superadmin only).
     */
    public function update(Request $request, $id)
    {
        // Only Superadmin can update applications
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,denied',
            'admin_note' => 'nullable|string',
        ]);

        $application = SquaresAdminApplication::findOrFail($id);

        $application->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // If approved, update user's role to Square Admin (role_id = 2)
        if ($request->status === 'approved') {
            User::where('id', $application->user_id)->update(['role_id' => 2]);
        }

        return response()->json([
            'message' => 'Application ' . $request->status . ' successfully.',
            'data' => $application->fresh(['user', 'reviewer']),
        ]);
    }
}
