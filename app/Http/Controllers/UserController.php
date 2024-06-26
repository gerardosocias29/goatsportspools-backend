<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

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

    public function update_profile(Request $request) {
        $currentUser = Auth::user();

        $currentUser->name = $request->first_name . " " . $request->last_name;
        $currentUser->email = $request->email;
        $currentUser->phone = $request->phone;
        $currentUser->first_name = $request->first_name;
        $currentUser->last_name = $request->last_name;
        $currentUser->address = $request->address;
        $currentUser->city = $request->city;
        $currentUser->state = $request->state;
        $currentUser->zipcode = $request->zipcode;
        $currentUser->username = $request->username;
        $currentUser->update();

        return response()->json(["status" => true, "message" => 'Update profile successful.', 'user' => $currentUser]);
    }

    public function update_password(Request $request) {
        $currentUser = Auth::user();

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        $currentUser->password = bcrypt($request->password);
        $currentUser->save();

        return response()->json(["status" => true, 'message' => 'Password updated successfully']);
    }  

    public function update_image(Request $request) {
        $currentUser = Auth::user();

        // Validate the request to ensure a file is uploaded
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle the file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('profile_images', 'public');

            $backendUrl = env('STORAGE_URL');
            $imageUrl = $backendUrl . '/' . $path;
            
            $currentUser->avatar = $imageUrl;
            $currentUser->save();

            return response()->json(['message' => 'Profile image updated successfully', 'user' => $currentUser, "status" => true]);
        }

        return response()->json(['message' => 'No file uploaded', "status" => false]);
    }

    public function getUserDetails(Request $request)
    {
        // Retrieve user details from the request attributes (set by the middleware)
        $user = $request->attributes->get('user');
        // Return user details as JSON
        $newUser = User::updateOrCreate(
            [ 'email' => $user->email, "clerk_id" => $user->id ],
            [
                'name' => $user->full_name,
                'phone' => $user->phone,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'role_id' => 2,
            ]
        );

        $token = JWTAuth::fromUser($newUser);

        return response()->json(['token' => $token]);
    }

    public function getToken(Request $request) {
        return response()->json(["user" => $request->avatar]);
    }

    public function getUsers(Request $request) {
        $filter = json_decode($request->filter);
        $usersQuery = User::query();

        $usersQuery = $this->applyFilters($usersQuery, $filter);
        $users = $usersQuery->paginate(($filter->rows), ['*'], 'page', ($filter->page + 1));
        
        return response($users);
    }
    

    private function applyFilters($query, $filter) {
        if (!empty($filter->filters->global->value)) {
            $query->where(function (Builder $query) use ($filter) {
                $value = '%' . $filter->filters->global->value . '%';
                $user = new User();
                foreach ($user->getFillable() as $column) {
                    $query->orWhere($column, 'LIKE', $value);
                }
            });
        }
        return $query;
    }
}
