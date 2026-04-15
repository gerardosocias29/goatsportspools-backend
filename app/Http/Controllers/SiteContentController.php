<?php

namespace App\Http\Controllers;

use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SiteContentController extends Controller
{
    /**
     * Allowed content keys. Prevents arbitrary key creation from client.
     */
    private const ALLOWED_KEYS = [
        'playoff_how_this_works',
        'playoff_faqs',
    ];

    /**
     * Public read — GET /api/site-content/{key}
     */
    public function show($key)
    {
        if (!in_array($key, self::ALLOWED_KEYS, true)) {
            return response()->json(['status' => false, 'message' => 'Unknown content key.'], 404);
        }

        $content = SiteContent::where('key', $key)->first();
        return response()->json([
            'status' => true,
            'data' => [
                'key' => $key,
                'title' => $content->title ?? null,
                'body' => $content->body ?? '',
                'updated_at' => $content->updated_at,
            ],
        ]);
    }

    /**
     * Admin list — GET /api/admin/site-content
     */
    public function index()
    {
        $this->requireSuperadmin();
        $items = SiteContent::whereIn('key', self::ALLOWED_KEYS)->get();
        return response()->json(['status' => true, 'data' => $items]);
    }

    /**
     * Admin update — PUT /api/admin/site-content/{key}
     * Body: { body: "<html>", title?: "..." }
     */
    public function update(Request $request, $key)
    {
        $this->requireSuperadmin();

        if (!in_array($key, self::ALLOWED_KEYS, true)) {
            return response()->json(['status' => false, 'message' => 'Unknown content key.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
            'title' => 'sometimes|string|max:150',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $content = SiteContent::updateOrCreate(
            ['key' => $key],
            [
                'body' => $request->input('body'),
                'title' => $request->input('title', SiteContent::where('key', $key)->value('title')),
                'updated_by' => Auth::id(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Content saved.',
            'data' => $content,
        ]);
    }

    private function requireSuperadmin()
    {
        $user = Auth::user();
        if (!$user || $user->role_id !== 1) {
            abort(403, 'Superadmin access required.');
        }
    }
}
