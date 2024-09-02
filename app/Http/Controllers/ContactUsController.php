<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsMail;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    public function send(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please fill-up the required fields.'
            ]);
        }

        // Get the validated data
        $data = $validator->validated();

        // Send an email or store the data
        try {
            Mail::to(["goatadmin@goatsportspools.com", "MarkrMahomes@gmail.com", "titoysemail@yahoo.com", "g.socias29@gmail.com"])->send(new ContactUsMail($data));

            return response()->json([
                'status' => true,
                'message' => 'Your message has been sent successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send your message, please try again later.',
            ]);
        }
    }
}