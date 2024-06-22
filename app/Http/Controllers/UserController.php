<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{   
    public function validate_token() {
        $user = Auth::user();
        return response()->json(["status" => true, "message" => "Authenticated!"]);
    }

    public function me_user() {
        $user = Auth::user();
        return response()->json(["status" => true, "user" => $user]);
    }
}
