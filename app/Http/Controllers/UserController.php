<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{User, Role, RoleModule, BalanceHistory, LeagueParticipant};
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{   
    public function getData() {
        $total_players = LeagueParticipant::count();
        $total_buyin = BalanceHistory::where('type', 'buyin')->where('amount', 3000)->count();
        $total_rebuys = BalanceHistory::where('type', 'rebuy')->count();

        return response()->json([
            "p" => $total_players,
            "b" => $total_buyin,
            "r" => $total_rebuys,
        ]);
    }

    public function validate_token() {
        $user = Auth::user();
        return response()->json(["status" => true, "message" => "Authenticated!"]);
    }

    public function me_user() {
        $user = Auth::user();
        $roles = Role::where('id', $user->role_id)->first();
        
        $modules = RoleModule::with(['sub_modules'])
            ->whereIn('id', $roles->allowed_modules)
            ->select('name', 'page', 'icon', 'id', 'parent_id')
            ->get();
        // Check if user has role_id = 3
        if ($user->role_id == 3 || $user->role_id == 2) {
            // Check if user is in a league
            $isInLeague = LeagueParticipant::where('user_id', $user->id)->exists();
            
            if ($isInLeague) {
                // If user is in a league, only show module ids [2, 3, 5]
                $modules = $modules->filter(function($module) use ($user) {
                    return in_array($module->id, $user->role_id == 3 ? [1, 2, 3, 4, 6, 9] : [1, 2, 3, 4, 6, 7, 8, 9]);
                });
                $modules = $modules->values();

            } else {
                // If user is not in a league, hide module ids [2, 3, 5]
                $modules = $modules->filter(function($module) {
                    return !in_array($module->id, [2, 4, 6]);
                });
                $modules = $modules->values();

            }
        }

        $user->modules = $modules;

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
        $user = $request->attributes->get('user');
        $newUser = User::with(['role.sub_modules'])->where('email', $user->email)->where('clerk_id', $user->id)->first();
        if(!$newUser){
            $newUser = new User();
            $newUser->email = $user->email;
            $newUser->role_id = 3;
            $newUser->clerk_id = $user->id;
            $newUser->name = $user->full_name ?? '';
            $newUser->phone = $user->phone ?? '';
            $newUser->first_name = $user->first_name ?? '';
            $newUser->last_name = $user->last_name ?? '';
            $newUser->username = $user->username ?? '';
            $newUser->avatar = $user->avatar ?? '';

            $newUser->save();

        } else {
            $newUser->name = $user->full_name ?? '';
            $newUser->phone = $user->phone ?? '';
            $newUser->first_name = $user->first_name ?? '';
            $newUser->last_name = $user->last_name ?? '';
            $newUser->username = $user->username ?? '';
            $newUser->avatar = $user->avatar ?? '';

            $newUser->update();
        }

        $token = JWTAuth::fromUser($newUser);

        return response()->json(['token' => $token, 'user' => $newUser, 'clerk_user' => $user]);
    }

    public function getToken(Request $request) {
        return response()->json(["user" => $request->avatar]);
    }

    public function getLeagueAdmins() {
        $users = User::select('id','name','avatar','username','email')->where('role_id', '=', 2)->get();
        return response($users);
    }

    public function getUsers(Request $request) {
        $filter = json_decode($request->filter);
        $usersQuery = User::with(['role' => function($query) {
            $query->select('id', 'name');
        }])->where('role_id', '!=', 1);

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

    public function getCardData() { 
        $cardData = [
            "active_users" => User::where('role_id', '!=', 1)->count(),
            "active_league_admin" => User::where('role_id', 2)->count()
        ];

        return response()->json($cardData);
    }

    public function updateRole(Request $request, $user_id) {
        // check loggedIn user permissions
        $currentUser = Auth::user()->load(['role']);
        if($currentUser->role->id != 1){
            return response(["status" => false, "message" => "You don't have enough permission to update!"]);
        }

        $user = User::where('id', $user_id)->first();
        if(!$user){
            return response(["status" => false, "message" => "User not found!"]);
        }

        $user->role_id = 2;
        $user->update();

        return response(["status" => true, "message" => "User role updated successfully to League Admin!"]);
    }

    public static function updateBalance($user_id, $amount) {
        $balanceHistory = new BalanceHistory();
        $balanceHistory->user_id = $user_id;
        $balanceHistory->amount = $amount;
        $balanceHistory->save();

        $user = User::where('id', $user_id)->first();
        $user->balance = $user->balance + $amount;
        $user->update();
    }
}
