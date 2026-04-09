<?php

namespace App\Http\Controllers;

use App\Models\PlayoffAdminApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PlayoffAdminApplicationController extends Controller
{
    /**
     * Get current user's playoff application status.
     */
    public function myStatus()
    {
        $application = PlayoffAdminApplication::where('user_id', Auth::id())->first();

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
     * Submit a new playoff admin application.
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
        $existingApplication = PlayoffAdminApplication::where('user_id', Auth::id())->first();

        if ($existingApplication) {
            return response()->json([
                'message' => 'You already have a pending playoff admin application.',
            ], 400);
        }

        $application = PlayoffAdminApplication::create([
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'reason' => $request->reason,
            'experience' => $request->experience,
            'status' => 'pending',
        ]);

        // Send notification email to all admins
        try {
            $adminEmails = explode(',', env('ADMIN_EMAIL', 'admin@goatsportspools.com'));
            foreach ($adminEmails as $adminEmail) {
                $adminEmail = trim($adminEmail);
                if (!empty($adminEmail)) {
                    Mail::raw(
                        "New NBA Playoff Admin Application\n\nName: {$application->full_name}\nEmail: {$application->email}\nReason: {$application->reason}\nExperience: " . ($application->experience ?? 'N/A'),
                        function ($message) use ($adminEmail) {
                            $message->to($adminEmail)
                                ->subject('New NBA Playoff Admin Application - OKRNG');
                        }
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send playoff admin application email to admin: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Application submitted successfully.',
            'data' => $application,
        ], 201);
    }

    /**
     * List all playoff applications (Superadmin only).
     */
    public function index(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $status = $request->query('status');

        $query = PlayoffAdminApplication::with(['user', 'reviewer']);

        if ($status) {
            $query->where('status', $status);
        }

        $applications = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $applications,
        ]);
    }

    /**
     * Update playoff application status (Superadmin only).
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,denied',
            'admin_note' => 'nullable|string',
        ]);

        $application = PlayoffAdminApplication::findOrFail($id);

        $application->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // If approved, set is_playoff_admin flag on user (does NOT touch role_id)
        if ($request->status === 'approved') {
            User::where('id', $application->user_id)->update(['is_playoff_admin' => true]);
        }

        return response()->json([
            'message' => 'Application ' . $request->status . ' successfully.',
            'data' => $application->fresh(['user', 'reviewer']),
        ]);
    }
}
