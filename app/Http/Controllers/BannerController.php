<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    /**
     * Get active banners for display (public endpoint).
     * GET /api/banners
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = $request->query('page', 'all');

        $banners = Banner::displayable()
            ->forPage($page)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $banners
        ]);
    }

    /**
     * Get all banners for admin management.
     * GET /api/banners/manage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manage()
    {
        $banners = Banner::with('creator:id,username,email')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $banners
        ]);
    }

    /**
     * Create a new banner.
     * POST /api/banners
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'variant' => 'nullable|string|in:primary,success,warning,info,promo',
            'page' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,hidden',
            'priority' => 'nullable|integer|min:0|max:100',
            'dismissible' => 'nullable|boolean',
            'action_text' => 'nullable|string|max:100',
            'action_url' => 'nullable|string|max:500',
        ]);

        $banner = Banner::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'variant' => $validated['variant'] ?? 'primary',
            'page' => $validated['page'] ?? 'all',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'priority' => $validated['priority'] ?? 0,
            'dismissible' => $validated['dismissible'] ?? false,
            'action_text' => $validated['action_text'] ?? null,
            'action_url' => $validated['action_url'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Banner created successfully',
            'data' => $banner
        ], 201);
    }

    /**
     * Get a single banner.
     * GET /api/banners/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $banner = Banner::with('creator:id,username,email')->find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $banner
        ]);
    }

    /**
     * Update a banner.
     * PUT /api/banners/{id}
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'variant' => 'nullable|string|in:primary,success,warning,info,promo',
            'page' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,hidden',
            'priority' => 'nullable|integer|min:0|max:100',
            'dismissible' => 'nullable|boolean',
            'action_text' => 'nullable|string|max:100',
            'action_url' => 'nullable|string|max:500',
        ]);

        $banner->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Banner updated successfully',
            'data' => $banner->fresh()
        ]);
    }

    /**
     * Delete a banner (soft delete).
     * DELETE /api/banners/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        $banner->delete();

        return response()->json([
            'status' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }

    /**
     * Toggle banner status (active/hidden).
     * PATCH /api/banners/{id}/toggle-status
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => false,
                'message' => 'Banner not found'
            ], 404);
        }

        $banner->status = $banner->status === 'active' ? 'hidden' : 'active';
        $banner->save();

        return response()->json([
            'status' => true,
            'message' => 'Banner status updated',
            'data' => $banner
        ]);
    }
}
