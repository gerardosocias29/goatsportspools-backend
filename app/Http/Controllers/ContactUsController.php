<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

class ContactUsController extends Controller
{
    public function send(Request $request)
    {
        $user = Auth::guard('api')->user();

        $key = 'contact-us-' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'status' => false,
                'message' => 'You have reached the daily limit. Please try again tomorrow.',
            ]); // Too Many Requests HTTP status code
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|min:2|max:255',
            'message' => 'required|string|min:2|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please fill-up the required fields.',
                'errors' => $validator->errors()
            ]);
        }

        // Increment the number of attempts with a 24-hour decay (1440 minutes)
        RateLimiter::hit($key, 1440); // 1440 minutes = 24 hours

        // Get the validated data
        $data = $validator->validated();

        $data['username'] = $user ? $user->username : "";
        $data['useremail'] = $user ? $user->email : "";
        $data['subject'] = "OKRNG Help Request from " . $data['name'];
        
        // Send an email or store the data
        try {
            // Get support emails from .env (comma-separated), fallback to default
            $supportEmails = env('SUPPORT_EMAILS', 'gerardo@okrng.com,sports@okrng.com,okrngsports@gmail.com,g.socias29@gmail.com');
            $recipients = array_map('trim', explode(',', $supportEmails));

            // Send email to each recipient individually
            foreach ($recipients as $recipient) {
                if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($recipient)->send(new ContactUsMail($data));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Your message has been sent successfully!',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send your message, please try again later.',
                'detail' => $e->getMessage()
            ]);
        }
    }
}