<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Get all settings (superadmin only)
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $settings = AppSetting::all()->map(function ($setting) {
            return [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->typed_value,
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        });

        return response()->json($settings);
    }

    /**
     * Get a specific setting value (public for certain keys)
     */
    public function get($key)
    {
        $publicKeys = ['march_madness_enabled'];

        $user = Auth::user();

        // Allow public access for certain keys
        if (!in_array($key, $publicKeys) && (!$user || $user->role_id != 1)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $value = AppSetting::getValue($key);

        if ($value === null) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        return response()->json(['key' => $key, 'value' => $value]);
    }

    /**
     * Update a setting (superadmin only)
     */
    public function update(Request $request, $key)
    {
        $user = Auth::user();

        if ($user->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'value' => 'required',
        ]);

        $setting = AppSetting::setValue($key, $request->value);

        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Setting updated successfully',
            'key' => $setting->key,
            'value' => $setting->typed_value,
        ]);
    }

    /**
     * Toggle a boolean setting (superadmin only)
     */
    public function toggle($key)
    {
        $user = Auth::user();

        if ($user->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $setting = AppSetting::where('key', $key)->first();

        if (!$setting || $setting->type !== 'boolean') {
            return response()->json(['error' => 'Setting not found or not a boolean'], 404);
        }

        $newValue = !$setting->typed_value;
        AppSetting::setValue($key, $newValue);

        return response()->json([
            'status' => true,
            'message' => 'Setting toggled successfully',
            'key' => $key,
            'value' => $newValue,
        ]);
    }
}
