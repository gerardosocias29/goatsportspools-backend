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
        $user = Auth::user();

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

        $data['username'] = "";
        $data['useremail'] = "";
        $data['subject'] = "GOAT Message from " . $data['name'];
        if(!empty($user)){
            $data['username'] = $user->username;
            $data['useremail'] = $user->email;
        }

        // Send an email or store the data
        try {
            Mail::to(["goatadmin@goatsportspools.com", "MarkrMahomes@gmail.com", "titoysemail@yahoo.com", "gerardo@goatsportspools.com"])->send(new ContactUsMail($data));

            return response()->json([
                'status' => true,
                'message' => 'Your message has been sent successfully!',
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